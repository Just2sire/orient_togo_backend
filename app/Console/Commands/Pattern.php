<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

#[Signature('app:pattern
        {name? : Nom du modèle en PascalCase (ex: Invoice, ProductCategory)}
        {--full          : Modèle + migration + Service + Controller + Requests + Resource}
        {--all           : Identique à --full + factory, seeder, test, policy, repository}
        {--migration     : Génère la migration}
        {--factory       : Génère la factory}
        {--seeder        : Génère le seeder}
        {--request       : Génère les Form Requests (Store + Update)}
        {--resource      : Génère l\'API Resource}
        {--repository    : Génère le Repository pattern (Interface + Implémentation + Binding)}
        {--test          : Génère les tests Pest}
        {--policy        : Génère la Policy}
        {--swagger       : Génère les annotations Swagger/OpenAPI}
        {--custom-fields= : Champs personnalisés pour Swagger (format: name:type,email:string,age:integer)}
        {--soft-delete   : Ajoute SoftDeletes au modèle}
        {--force         : Écrase les fichiers existants}
        {--dry-run       : Affiche les fichiers qui seraient créés, sans les créer}
        {--rollback      : En cas d\'erreur, supprime les fichiers déjà créés pendant cette session}')]
#[Description('Génère le pattern API complet avec documentation Swagger automatique (Model, Repository, Service, Controller, Requests, Resource, Policy, Tests, Routes)')]
class Pattern extends Command
{
    /** @var array<string, true> Fichiers créés durant cette session (pour rollback) */
    private array $created = [];

    /** @var array<string, string> */
    private array $skipped = [];

    /** @var array<string, string> */
    private array $failed = [];

    /** @var array<string, string> Champs personnalisés pour Swagger */
    private array $customFields = [];

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    // =========================================================================
    // Point d'entrée
    // =========================================================================

    public function handle(): int
    {
        $name = $this->resolveName();

        if ($name === null) {
            return self::FAILURE;
        }

        // Parse custom fields
        $this->parseCustomFields();

        if ($this->option('dry-run')) {
            return $this->runDryRun($name);
        }

        $this->components->info("Génération du pattern pour : <fg=cyan>{$name}</>");
        if ($this->option('swagger')) {
            $this->components->info('🔖 Avec annotations Swagger/OpenAPI');
        }
        $this->newLine();

        $this->ensureApiResponseTraitExists();

        // Installer L5-Swagger si nécessaire
        if ($this->option('swagger')) {
            $this->ensureSwaggerInstalled();
        }

        // --- Modèle ---
        $createModel = $this->option('full') || $this->option('all');

        if ($createModel) {
            $this->makeModel($name);
        } elseif (! $this->modelExists($name)) {
            $this->components->error("Le modèle {$name} n'existe pas. Utilisez --full pour le créer.");

            return self::FAILURE;
        }

        // --- Repository ---
        if ($this->wantsRepository()) {
            $this->makeRepository($name);
        }

        // --- Service & Controller ---
        $this->makeService($name);
        $this->makeController($name);

        // --- Optionnels ---
        if ($this->wantsRequest()) {
            $this->makeFormRequests($name);
        }
        if ($this->wantsResource()) {
            $this->makeResource($name);
        }
        if ($this->wantsTest()) {
            $this->makeTest($name);
        }
        if ($this->wantsPolicy()) {
            $this->makePolicy($name);
        }

        // --- Routes ---
        $this->appendRoutes($name);

        // --- Configuration Swagger ---
        if ($this->option('swagger')) {
            $this->ensureSwaggerConfig($name);
        }

        $this->newLine();
        $this->printReport($name);

        // --- Rollback si demandé et qu'il y a des échecs ---
        if (! empty($this->failed) && $this->option('rollback')) {
            $this->runRollback();
        }

        return empty($this->failed) ? self::SUCCESS : self::FAILURE;
    }

    // =========================================================================
    // Résolution du nom et custom fields
    // =========================================================================

    private function resolveName(): ?string
    {
        $name = $this->argument('name')
            ?? $this->ask('Quel est le nom du modèle ? <fg=gray>(PascalCase, ex: Invoice)</>');

        if (empty($name)) {
            $this->components->error('Le nom du modèle est requis.');

            return null;
        }

        $name = Str::studly($name);

        $reserved = ['Model', 'Controller', 'Request', 'Resource', 'Service', 'Repository', 'Command', 'Job', 'Event', 'Listener'];

        if (in_array($name, $reserved, true)) {
            $this->components->error("Le nom \"{$name}\" est réservé par Laravel.");

            return null;
        }

        return $name;
    }

    private function parseCustomFields(): void
    {
        $fieldsOption = $this->option('custom-fields');

        if (! $fieldsOption) {
            return;
        }

        // Format: name:string,email:string,age:integer
        $pairs = explode(',', $fieldsOption);

        foreach ($pairs as $pair) {
            if (str_contains($pair, ':')) {
                [$field, $type] = explode(':', $pair, 2);
                $this->customFields[trim($field)] = trim($type);
            }
        }
    }

    // =========================================================================
    // Mode aperçu (dry-run)
    // =========================================================================

    private function runDryRun(string $name): int
    {
        $this->components->info('Mode aperçu — aucun fichier ne sera créé.');
        $this->newLine();

        $files = $this->collectExpectedFiles($name);

        $this->line('  <fg=cyan>Fichiers qui seraient générés :</>');
        $this->newLine();

        foreach ($files as [$label, $path]) {
            $exists = $this->files->exists(base_path($path));
            $icon = $exists ? '<fg=yellow>~</>' : '<fg=green>+</>';
            $status = $exists ? '<fg=yellow>existe déjà</>' : '<fg=green>nouveau</>';
            $this->line("  {$icon} {$path} <fg=gray>({$status})</>");
        }

        $this->newLine();
        $this->components->twoColumnDetail(
            'Pour créer les fichiers',
            'php artisan make:pattern '.$name.' --full --swagger'
        );

        return self::SUCCESS;
    }

    /** @return array<int, array{string, string}> */
    private function collectExpectedFiles(string $name): array
    {
        $files = [
            ['Trait ApiResponse', 'app/Traits/ApiResponse.php'],
        ];

        if ($this->option('full') || $this->option('all')) {
            $files[] = ['Modèle', "app/Models/{$name}.php"];
        }

        if ($this->wantsRepository()) {
            $files[] = ['RepositoryInterface', "app/Repositories/Contracts/{$name}RepositoryInterface.php"];
            $files[] = ['Repository',          "app/Repositories/{$name}Repository.php"];
            $files[] = ['RepositoryProvider',  'app/Providers/RepositoryServiceProvider.php'];
        }

        $files[] = ['Service',    "app/Services/{$name}Service.php"];
        $files[] = ['Controller', "app/Http/Controllers/Api/{$name}Controller.php"];

        if ($this->wantsRequest()) {
            $files[] = ['StoreRequest',  "app/Http/Requests/Store{$name}Request.php"];
            $files[] = ['UpdateRequest', "app/Http/Requests/Update{$name}Request.php"];
        }
        if ($this->wantsResource()) {
            $files[] = ['Resource', "app/Http/Resources/{$name}Resource.php"];
        }
        if ($this->wantsTest()) {
            $files[] = ['Test', "tests/Feature/{$name}Test.php"];
        }
        if ($this->wantsPolicy()) {
            $files[] = ['Policy', "app/Policies/{$name}Policy.php"];
        }

        return $files;
    }

    // =========================================================================
    // Swagger Installation & Configuration
    // =========================================================================

    private function ensureSwaggerInstalled(): void
    {
        if ($this->files->exists(config_path('l5-swagger.php'))) {
            return;
        }

        $this->components->warn('L5-Swagger n\'est pas installé.');

        if ($this->confirm('Voulez-vous installer darkaonline/l5-swagger maintenant ?', true)) {
            $this->components->task('Installation de L5-Swagger', function () {
                exec('composer require darkaonline/l5-swagger', $output, $code);

                return $code === 0;
            });

            if ($this->files->exists(config_path('l5-swagger.php'))) {
                $this->components->info('✓ L5-Swagger installé avec succès.');
            } else {
                $this->components->warn('Publication de la configuration...');
                $this->call('vendor:publish', ['--provider' => 'L5Swagger\L5SwaggerServiceProvider']);
            }
        } else {
            $this->components->warn('Vous devrez installer L5-Swagger manuellement : composer require darkaonline/l5-swagger');
        }
    }

    private function ensureSwaggerConfig(string $name): void
    {
        $baseController = app_path('Http/Controllers/Controller.php');

        if (! $this->files->exists($baseController)) {
            return;
        }

        $content = $this->files->get($baseController);

        // Vérifier si les annotations globales existent déjà
        if (str_contains($content, '@OA\Info')) {
            $this->components->twoColumnDetail('Swagger Config', '<fg=yellow>déjà présent</>');

            return;
        }

        // Injecter les annotations globales
        $globalAnnotations = $this->stubSwaggerGlobalAnnotations();

        if (preg_match('/(class\s+Controller\s+extends\s+BaseController)/', $content, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            $content = substr_replace($content, $globalAnnotations."\n", $pos, 0);
            $this->files->put($baseController, $content);
            $this->components->twoColumnDetail('Swagger Config', '<fg=green>ajouté à Controller.php</>');
        }
    }

    // =========================================================================
    // Rollback
    // =========================================================================

    private function runRollback(): void
    {
        $this->newLine();
        $this->components->warn('Rollback en cours — suppression des fichiers créés durant cette session...');

        foreach (array_keys($this->created) as $relative) {
            $path = base_path($relative);

            if ($this->files->exists($path)) {
                $this->files->delete($path);
                $this->line("  <fg=red>-</> {$relative} supprimé");
            }
        }

        $this->components->info('Rollback terminé.');
    }

    // =========================================================================
    // Générateurs de fichiers
    // =========================================================================

    private function makeModel(string $name): void
    {
        $options = ['name' => $name, '--no-interaction' => true];

        if ($this->option('migration') || $this->option('full') || $this->option('all')) {
            $options['--migration'] = true;
        }
        if ($this->option('factory') || $this->option('all')) {
            $options['--factory'] = true;
        }
        if ($this->option('seeder') || $this->option('all')) {
            $options['--seed'] = true;
        }

        $this->call('make:model', $options);

        $this->patchModel($name);
        $this->created["app/Models/{$name}.php"] = true;
    }

    private function patchModel(string $name): void
    {
        $path = app_path("Models/{$name}.php");

        if (! $this->files->exists($path)) {
            return;
        }

        $content = $this->files->get($path);

        foreach (
            [
                'use App\\Models\\Traits\\HasOrganization;',
                'use Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;',
            ] as $import
        ) {
            if (! str_contains($content, $import)) {
                $content = str_replace(
                    'use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;',
                    $import."\nuse Illuminate\\Database\\Eloquent\\Factories\\HasFactory;",
                    $content
                );
            }
        }

        $content = preg_replace(
            '/use HasFactory;/',
            'use HasFactory, HasUuids, HasOrganization;',
            $content
        );

        $content = preg_replace(
            "/protected \\\$fillable\s*=\s*\[.*?\];/s",
            'protected $guarded = [];',
            $content
        );

        if ($this->option('soft-delete') && ! str_contains($content, 'SoftDeletes')) {
            $content = str_replace(
                'use Illuminate\\Database\\Eloquent\\Model;',
                "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;",
                $content
            );
            $content = str_replace(
                'use HasFactory, HasUuids, HasOrganization;',
                'use HasFactory, HasUuids, HasOrganization, SoftDeletes;',
                $content
            );
        }

        $this->files->put($path, $content);
        $this->components->twoColumnDetail('Modèle patché', "<fg=green>{$name}</>");
    }

    // --- Repository ----------------------------------------------------------

    private function makeRepository(string $name): void
    {
        $this->write(
            label: "{$name}RepositoryInterface",
            path: app_path("Repositories/Contracts/{$name}RepositoryInterface.php"),
            relative: "app/Repositories/Contracts/{$name}RepositoryInterface.php",
            content: $this->stubRepositoryInterface($name),
        );

        $this->write(
            label: "{$name}Repository",
            path: app_path("Repositories/{$name}Repository.php"),
            relative: "app/Repositories/{$name}Repository.php",
            content: $this->stubRepository($name),
        );

        $this->ensureRepositoryProviderBinding($name);
    }

    private function ensureRepositoryProviderBinding(string $name): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (! $this->files->exists($providerPath)) {
            $this->write(
                label: 'RepositoryServiceProvider',
                path: $providerPath,
                relative: 'app/Providers/RepositoryServiceProvider.php',
                content: $this->stubRepositoryProvider($name),
            );
            $this->warnBootstrapRegistration();

            return;
        }

        $content = $this->files->get($providerPath);
        $interface = "App\\Repositories\\Contracts\\{$name}RepositoryInterface";
        $impl = "App\\Repositories\\{$name}Repository";

        if (str_contains($content, "{$name}RepositoryInterface")) {
            $this->components->twoColumnDetail("{$name} binding", '<fg=yellow>déjà présent</>');

            return;
        }

        $binding = "\n        \$this->app->bind(\n            {$name}RepositoryInterface::class,\n            {$name}Repository::class,\n        );";

        $useStatements = "use {$interface};\nuse {$impl};\n";

        if (! str_contains($content, "use {$interface}")) {
            $content = preg_replace(
                '/(namespace App\\\\Providers;\n)/',
                "$1\n{$useStatements}",
                $content
            );
        }

        if (preg_match('/(public function register\(\): void\s*\{)/', $content)) {
            $content = preg_replace(
                '/(public function register\(\): void\s*\{)/',
                "$1{$binding}",
                $content
            );
        }

        $this->files->put($providerPath, $content);
        $this->components->twoColumnDetail("{$name} binding ajouté", '<fg=green>✓</>');
    }

    private function warnBootstrapRegistration(): void
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (! $this->files->exists($bootstrapPath)) {
            return;
        }

        $content = $this->files->get($bootstrapPath);

        if (str_contains($content, 'RepositoryServiceProvider')) {
            return;
        }

        $this->newLine();
        $this->components->warn('Pensez à enregistrer le provider dans bootstrap/app.php :');
        $this->line('  <fg=gray>$app->register(App\\Providers\\RepositoryServiceProvider::class);</>');
        $this->newLine();
    }

    // --- Service -------------------------------------------------------------

    private function makeService(string $name): void
    {
        $this->write(
            label: 'Service',
            path: app_path("Services/{$name}Service.php"),
            relative: "app/Services/{$name}Service.php",
            content: $this->stubService($name),
        );
    }

    // --- Controller ----------------------------------------------------------

    private function makeController(string $name): void
    {
        $this->write(
            label: 'Controller',
            path: app_path("Http/Controllers/Api/{$name}Controller.php"),
            relative: "app/Http/Controllers/Api/{$name}Controller.php",
            content: $this->stubController($name),
        );
    }

    // --- Requests ------------------------------------------------------------

    private function makeFormRequests(string $name): void
    {
        $this->write(
            label: "Store{$name}Request",
            path: app_path("Http/Requests/Store{$name}Request.php"),
            relative: "app/Http/Requests/Store{$name}Request.php",
            content: $this->stubStoreRequest($name),
        );

        $this->write(
            label: "Update{$name}Request",
            path: app_path("Http/Requests/Update{$name}Request.php"),
            relative: "app/Http/Requests/Update{$name}Request.php",
            content: $this->stubUpdateRequest($name),
        );
    }

    // --- Resource ------------------------------------------------------------

    private function makeResource(string $name): void
    {
        $this->write(
            label: "{$name}Resource",
            path: app_path("Http/Resources/{$name}Resource.php"),
            relative: "app/Http/Resources/{$name}Resource.php",
            content: $this->stubResource($name),
        );
    }

    // --- Test ----------------------------------------------------------------

    private function makeTest(string $name): void
    {
        $this->write(
            label: "{$name}Test",
            path: base_path("tests/Feature/{$name}Test.php"),
            relative: "tests/Feature/{$name}Test.php",
            content: $this->stubTest($name),
        );
    }

    // --- Policy --------------------------------------------------------------

    private function makePolicy(string $name): void
    {
        $this->write(
            label: "{$name}Policy",
            path: app_path("Policies/{$name}Policy.php"),
            relative: "app/Policies/{$name}Policy.php",
            content: $this->stubPolicy($name),
        );
    }

    // --- Routes --------------------------------------------------------------

    private function appendRoutes(string $name): void
    {
        $routePath = base_path('routes/api.php');

        if (! $this->files->exists($routePath)) {
            $this->components->warn('routes/api.php introuvable. Ajoutez manuellement les routes.');

            return;
        }

        $content = $this->files->get($routePath);
        $controllerFqn = "App\\Http\\Controllers\\Api\\{$name}Controller";
        $kebab = Str::kebab(Str::plural($name));
        $routeName = Str::snake(Str::plural($name), '-');

        if (str_contains($content, "{$name}Controller")) {
            $this->components->twoColumnDetail('Routes', '<fg=yellow>déjà présentes</>');

            return;
        }

        $route = "\n    Route::apiResource('{$kebab}', {$controllerFqn}::class)"
            ."->names('{$routeName}');";

        if (preg_match(
            "/(Route::middleware\(['\"]auth:sanctum['\"]\)->group\(function\s*\(\)\s*\{)(.*?)(\}\);)/s",
            $content,
            $m,
            PREG_OFFSET_CAPTURE
        )) {
            $pos = $m[0][1] + strlen($m[1][0]) + strlen($m[2][0]);
            $content = substr_replace($content, $route, $pos, 0);
        } else {
            $content .= "\n\nRoute::middleware('auth:sanctum')->group(function () {{$route}\n});\n";
        }

        $this->files->put($routePath, $content);
        $this->components->twoColumnDetail("Route ajoutée <fg=gray>/api/{$kebab}</>", '<fg=green>✓</>');
    }

    // --- Trait ApiResponse ---------------------------------------------------

    private function ensureApiResponseTraitExists(): void
    {
        $path = app_path('Traits/ApiResponse.php');

        if ($this->files->exists($path)) {
            return;
        }

        $this->write(
            label: 'Trait ApiResponse',
            path: $path,
            relative: 'app/Traits/ApiResponse.php',
            content: $this->stubApiResponseTrait(),
        );
    }

    // =========================================================================
    // Écriture fichier avec gestion force / skip / rollback
    // =========================================================================

    private function write(string $label, string $path, string $relative, string $content): void
    {
        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->twoColumnDetail($label, '<fg=yellow>ignoré (existe déjà — utilisez --force)</>');
            $this->skipped[$relative] = 'Utilisez --force pour écraser.';

            return;
        }

        try {
            $this->files->ensureDirectoryExists(dirname($path));
            $this->files->put($path, $content);
            $this->components->twoColumnDetail($label, '<fg=green>créé</>');
            $this->created[$relative] = true;
        } catch (\Throwable $e) {
            $this->components->twoColumnDetail($label, '<fg=red>ERREUR : '.$e->getMessage().'</>');
            $this->failed[$relative] = $e->getMessage();

            if ($this->option('rollback')) {
                $this->runRollback();
                $this->components->error('Rollback effectué. Abandon.');
                exit(self::FAILURE);
            }
        }
    }

    // =========================================================================
    // Rapport final
    // =========================================================================

    private function printReport(string $name): void
    {
        $kebab = Str::kebab(Str::plural($name));

        if (! empty($this->created)) {
            $this->components->info(count($this->created).' fichier(s) créé(s) :');
            foreach (array_keys($this->created) as $f) {
                $this->line("  <fg=green>+</> {$f}");
            }
            $this->newLine();
        }

        if (! empty($this->skipped)) {
            $this->components->warn(count($this->skipped).' fichier(s) ignoré(s) :');
            foreach (array_keys($this->skipped) as $f) {
                $this->line("  <fg=yellow>~</> {$f}");
            }
            $this->newLine();
        }

        if (! empty($this->failed)) {
            $this->components->error(count($this->failed).' erreur(s) :');
            foreach ($this->failed as $f => $msg) {
                $this->line("  <fg=red>✗</> {$f} : {$msg}");
            }
            $this->newLine();
        }

        $this->line('<fg=cyan>Routes disponibles :</>');
        $actions = ['index', 'store', 'show', 'update', 'destroy'];
        $methods = ['GET', 'POST', 'GET /{id}', 'PUT /{id}', 'DELETE /{id}'];

        foreach ($methods as $i => $method) {
            $this->line("  <fg=gray>{$method}</>\t/api/{$kebab}\t→ {$actions[$i]}");
        }

        $this->newLine();
        $this->line('<fg=magenta>Prochaines étapes :</>');
        $step = 1;
        $this->line("  {$step}. Définir les règles dans Store/Update Requests");
        $step++;
        if ($this->wantsRepository()) {
            $this->line("  {$step}. Enregistrer RepositoryServiceProvider dans bootstrap/app.php");
            $step++;
            $this->line("  {$step}. Implémenter les méthodes métier dans {$name}Repository");
            $step++;
        }
        $this->line("  {$step}. Implémenter la logique métier dans le Service");
        $step++;
        $this->line("  {$step}. Ajuster les champs exposés dans la Resource");
        $step++;
        if ($this->option('swagger')) {
            $this->line("  {$step}. Générer la doc Swagger : <fg=cyan>php artisan l5-swagger:generate</>");
            $step++;
            $this->line("  {$step}. Accéder à la doc : <fg=cyan>http://localhost:8000/api/documentation</>");
            $step++;
        }
        if ($this->wantsTest()) {
            $this->line("  {$step}. php artisan test --filter={$name}Test");
            $step++;
        }
        $this->line("  {$step}. php artisan migrate");
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function modelExists(string $name): bool
    {
        return $this->files->exists(app_path("Models/{$name}.php"));
    }

    private function wantsRequest(): bool
    {
        return (bool) ($this->option('request') || $this->option('full') || $this->option('all'));
    }

    private function wantsResource(): bool
    {
        return (bool) ($this->option('resource') || $this->option('full') || $this->option('all'));
    }

    private function wantsTest(): bool
    {
        return (bool) ($this->option('test') || $this->option('all'));
    }

    private function wantsPolicy(): bool
    {
        return (bool) ($this->option('policy') || $this->option('all'));
    }

    private function wantsRepository(): bool
    {
        return (bool) ($this->option('repository') || $this->option('all'));
    }

    // =========================================================================
    // Stubs (avec Swagger)
    // =========================================================================

    // --- Swagger Global Annotations ------------------------------------------

    private function stubSwaggerGlobalAnnotations(): string
    {
        return <<<'PHP'
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Mon API Laravel 13",
 *     description="Documentation complète de l'API générée automatiquement",
 *     @OA\Contact(
 *         email="support@monapi.com",
 *         name="Support API"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Serveur de développement"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Authentification via Laravel Sanctum. Entrez le token Bearer."
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Une erreur est survenue.")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Les données fournies sont invalides."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *             @OA\Items(type="string", example="Le champ est obligatoire.")
 *         )
 *     )
 * )
 */
 
PHP;
    }

    // --- Repository Interface ------------------------------------------------

    private function stubRepositoryInterface(string $name): string
    {
        $var = Str::camel($name);
        $plural = Str::plural($var);

        return <<<PHP
<?php
 
namespace App\Repositories\Contracts;
 
use App\Models\\{$name};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
 
interface {$name}RepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  \$filters
     */
    public function paginate(array \$filters = [], int \$perPage = 15): LengthAwarePaginator;
 
    /**
     * Retourne tous les {$plural} sans pagination.
     *
     * @return Collection<int, {$name}>
     */
    public function all(array \$filters = []): Collection;
 
    /**
     * Trouve un(e) {$name} par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int \$id): {$name};
 
    /**
     * Crée un(e) {$name}.
     *
     * @param  array<string, mixed>  \$data
     */
    public function create(array \$data): {$name};
 
    /**
     * Met à jour un(e) {$name}.
     *
     * @param  array<string, mixed>  \$data
     */
    public function update({$name} \${$var}, array \$data): {$name};
 
    /**
     * Supprime un(e) {$name}.
     */
    public function delete({$name} \${$var}): bool;
}
PHP;
    }

    // --- Repository Eloquent -------------------------------------------------

    private function stubRepository(string $name): string
    {
        $var = Str::camel($name);

        return <<<PHP
<?php
 
namespace App\Repositories;
 
use App\Models\\{$name};
use App\Repositories\Contracts\\{$name}RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
 
class {$name}Repository implements {$name}RepositoryInterface
{
    public function __construct(private readonly {$name} \$model) {}
 
    public function paginate(array \$filters = [], int \$perPage = 15): LengthAwarePaginator
    {
        return \$this->model->query()
            ->when(\$filters['search'] ?? null, fn (\$q, \$v) => \$q->where('name', 'like', "%{\$v}%"))
            // Ajoutez d'autres filtres ici
            ->latest()
            ->paginate(\$perPage);
    }
 
    public function all(array \$filters = []): Collection
    {
        return \$this->model->query()
            ->when(\$filters['search'] ?? null, fn (\$q, \$v) => \$q->where('name', 'like', "%{\$v}%"))
            ->latest()
            ->get();
    }
 
    public function findOrFail(string|int \$id): {$name}
    {
        return \$this->model->findOrFail(\$id);
    }
 
    public function create(array \$data): {$name}
    {
        return DB::transaction(fn () => \$this->model->create(\$data));
    }
 
    public function update({$name} \${$var}, array \$data): {$name}
    {
        DB::transaction(fn () => \${$var}->update(\$data));
 
        return \${$var}->fresh();
    }
 
    public function delete({$name} \${$var}): bool
    {
        return DB::transaction(fn () => (bool) \${$var}->delete());
    }
}
PHP;
    }

    // --- Repository Service Provider -----------------------------------------

    private function stubRepositoryProvider(string $name): string
    {
        return <<<PHP
<?php
 
namespace App\Providers;
 
use App\Repositories\Contracts\\{$name}RepositoryInterface;
use App\Repositories\\{$name}Repository;
use Illuminate\Support\ServiceProvider;
 
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            {$name}RepositoryInterface::class,
            {$name}Repository::class,
        );
    }
 
    public function boot(): void
    {
        //
    }
}
PHP;
    }

    // --- Service -------------------------------------------------------------

    private function stubService(string $name): string
    {
        $var = Str::camel($name);
        $plural = Str::plural($var);
        $hasReq = $this->wantsRequest();
        $hasRes = $this->wantsResource();
        $hasRep = $this->wantsRepository();

        $storeArg = $hasReq ? "Store{$name}Request \$request" : 'array $data';
        $storeData = $hasReq ? '$request->validated()' : '$data';
        $updateArg = $hasReq ? "Update{$name}Request \$request" : 'array $data';
        $updateData = $hasReq ? '$request->validated()' : '$data';

        $useRequest = $hasReq
            ? "\nuse App\\Http\\Requests\\Store{$name}Request;\nuse App\\Http\\Requests\\Update{$name}Request;"
            : '';
        $useResource = $hasRes ? "\nuse App\\Http\\Resources\\{$name}Resource;" : '';

        $dataWrap = fn (string $expr) => $hasRes ? "new {$name}Resource({$expr})" : $expr;

        if ($hasRep) {
            $useRepository = "\nuse App\\Repositories\\Contracts\\{$name}RepositoryInterface;";
            $useModel = "\nuse App\\Models\\{$name};";
            $constructor = "    public function __construct(\n        private readonly {$name}RepositoryInterface \$repository\n    ) {}";
            $indexBody = "\$paginator = \$this->repository->paginate(\$request->all(), \$request->integer('per_page', 15));\n            return \$this->paginated(\$paginator".($hasRes ? ", {$name}Resource::class" : '').');';
            $showBody = "\$model = \$this->repository->findOrFail(\${$var}->id);\n            return \$this->success({$dataWrap('$model')}, '{$name} récupéré.');";
            $storeBody = "\$model = \$this->repository->create({$storeData});\n            return \$this->created({$dataWrap('$model')});";
            $updateBody = "\$model = \$this->repository->update(\${$var}, {$updateData});\n            return \$this->updated({$dataWrap('$model')});";
            $destroyBody = "\$this->repository->delete(\${$var});\n            return \$this->deleted();";
            $useDB = '';
            $showUse = "\${$var}";
        } else {
            $useRepository = '';
            $useModel = "\nuse App\\Models\\{$name};";
            $useDB = "\nuse Illuminate\\Support\\Facades\\DB;";
            $constructor = '';
            $indexBody = "\${$plural} = {$name}::query()\n                ->when(\$request->search, fn (\$q, \$v) => \$q->where('name', 'like', \"%{\$v}%\"))\n                ->latest()\n                ->paginate(\$request->integer('per_page', 15));\n            return \$this->paginated(\${$plural}".($hasRes ? ", {$name}Resource::class" : '').');';
            $showBody = "return \$this->success({$dataWrap('$'.$var)}, '{$name} récupéré.');";
            $storeBody = "\$model = DB::transaction(fn () => {$name}::create({$storeData}));\n            return \$this->created({$dataWrap('$model')});";
            $updateBody = "DB::transaction(fn () => \${$var}->update({$updateData}));\n            return \$this->updated({$dataWrap('$'.$var.'->fresh()')});";
            $destroyBody = "\${$var}->delete();\n            return \$this->deleted();";
            $showUse = "\${$var}";
        }

        $storeUse = $hasReq ? '$request' : '$data';
        $updateUse = $hasReq ? '$request, $'.$var : '$data, $'.$var;

        return <<<PHP
<?php
 
namespace App\Services;
{$useRequest}{$useResource}{$useRepository}{$useModel}{$useDB}
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
 
class {$name}Service
{
    use ApiResponse;
 
{$constructor}
 
    /**
     * Retourne la liste des {$plural} (avec filtres et pagination).
     */
    public function index(Request \$request): JsonResponse
    {
        return \$this->try(function () use (\$request) {
            {$indexBody}
        }, 'Impossible de récupérer les {$plural}.');
    }
 
    /**
     * Affiche un(e) {$name}.
     */
    public function show({$name} \${$var}): JsonResponse
    {
        return \$this->try(function () use ({$showUse}) {
            {$showBody}
        }, 'Impossible de récupérer ce {$var}.');
    }
 
    /**
     * Crée un(e) {$name}.
     */
    public function store({$storeArg}): JsonResponse
    {
        return \$this->try(function () use ({$storeUse}) {
            {$storeBody}
        }, 'Impossible de créer le {$var}.');
    }
 
    /**
     * Met à jour un(e) {$name}.
     */
    public function update({$name} \${$var}, {$updateArg}): JsonResponse
    {
        return \$this->try(function () use ({$updateUse}) {
            {$updateBody}
        }, 'Impossible de mettre à jour le {$var}.');
    }
 
    /**
     * Supprime un(e) {$name}.
     */
    public function destroy({$name} \${$var}): JsonResponse
    {
        return \$this->try(function () use (\${$var}) {
            {$destroyBody}
        }, 'Impossible de supprimer le {$var}.');
    }
}
PHP;
    }

    // --- Controller avec Swagger ---------------------------------------------

    private function stubController(string $name): string
    {
        $var = Str::camel($name);
        $plural = Str::plural($var);
        $kebab = Str::kebab(Str::plural($name));
        $hasReq = $this->wantsRequest();
        $hasSwagger = $this->option('swagger');

        $storeType = $hasReq ? "Store{$name}Request" : 'Request';
        $updateType = $hasReq ? "Update{$name}Request" : 'Request';

        if ($hasReq) {
            $useReq = "use App\\Http\\Requests\\Store{$name}Request;\nuse App\\Http\\Requests\\Update{$name}Request;";
        } else {
            $useReq = 'use Illuminate\\Http\\Request;';
        }

        // Swagger annotations
        $tagAnnotation = $hasSwagger ? "\n/**\n * @OA\\Tag(\n *     name=\"{$name}\",\n *     description=\"Gestion des {$plural}\"\n * )\n */" : '';

        $indexSwagger = $hasSwagger ? $this->swaggerIndexAnnotation($name) : '';
        $showSwagger = $hasSwagger ? $this->swaggerShowAnnotation($name) : '';
        $storeSwagger = $hasSwagger ? $this->swaggerStoreAnnotation($name) : '';
        $updateSwagger = $hasSwagger ? $this->swaggerUpdateAnnotation($name) : '';
        $destroySwagger = $hasSwagger ? $this->swaggerDestroyAnnotation($name) : '';

        return <<<PHP
<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
{$useReq}
use App\Models\\{$name};
use App\Services\\{$name}Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
{$tagAnnotation}
class {$name}Controller extends Controller
{
    public function __construct(
        private readonly {$name}Service \$service
    ) {}
 
{$indexSwagger}    public function index(Request \$request): JsonResponse
    {
        return \$this->service->index(\$request);
    }
 
{$showSwagger}    public function show({$name} \${$var}): JsonResponse
    {
        return \$this->service->show(\${$var});
    }
 
{$storeSwagger}    public function store({$storeType} \$request): JsonResponse
    {
        // \$this->authorize('create', {$name}::class);
 
        return \$this->service->store(\$request);
    }
 
{$updateSwagger}    public function update({$updateType} \$request, {$name} \${$var}): JsonResponse
    {
        // \$this->authorize('update', \${$var});
 
        return \$this->service->update(\${$var}, \$request);
    }
 
{$destroySwagger}    public function destroy({$name} \${$var}): JsonResponse
    {
        // \$this->authorize('delete', \${$var});
 
        return \$this->service->destroy(\${$var});
    }
}
PHP;
    }

    // --- Swagger Annotations Helpers -----------------------------------------

    private function swaggerIndexAnnotation(string $name): string
    {
        $plural = Str::plural(Str::camel($name));
        $kebab = Str::kebab(Str::plural($name));

        return <<<SWAGGER
    /**
     * @OA\Get(
     *     path="/api/{$kebab}",
     *     summary="Liste des {$plural}",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="get{$name}s",
     *     tags={"{$name}"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par nom",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/{$name}Resource")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
 
SWAGGER;
    }

    private function swaggerShowAnnotation(string $name): string
    {
        $var = Str::camel($name);
        $kebab = Str::kebab(Str::plural($name));

        return <<<SWAGGER
    /**
     * @OA\Get(
     *     path="/api/{$kebab}/{id}",
     *     summary="Détails d'un(e) {$name}",
     *     operationId="get{$name}",
     *     tags={"{$name}"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="{$name} trouvé(e)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/{$name}Resource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
 
SWAGGER;
    }

    private function swaggerStoreAnnotation(string $name): string
    {
        $kebab = Str::kebab(Str::plural($name));

        return <<<SWAGGER
    /**
     * @OA\Post(
     *     path="/api/{$kebab}",
     *     summary="Créer un(e) {$name}",
     *     operationId="create{$name}",
     *     tags={"{$name}"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Store{$name}Request")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="{$name} créé(e)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/{$name}Resource")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
 
SWAGGER;
    }

    private function swaggerUpdateAnnotation(string $name): string
    {
        $kebab = Str::kebab(Str::plural($name));

        return <<<SWAGGER
    /**
     * @OA\Put(
     *     path="/api/{$kebab}/{id}",
     *     summary="Mettre à jour un(e) {$name}",
     *     operationId="update{$name}",
     *     tags={"{$name}"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Update{$name}Request")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="{$name} mis(e) à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/{$name}Resource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
 
SWAGGER;
    }

    private function swaggerDestroyAnnotation(string $name): string
    {
        $kebab = Str::kebab(Str::plural($name));

        return <<<SWAGGER
    /**
     * @OA\Delete(
     *     path="/api/{$kebab}/{id}",
     *     summary="Supprimer un(e) {$name}",
     *     operationId="delete{$name}",
     *     tags={"{$name}"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="{$name} supprimé(e)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
 
SWAGGER;
    }

    // --- Store Request avec Swagger ------------------------------------------

    private function stubStoreRequest(string $name): string
    {
        $hasSwagger = $this->option('swagger');

        $fields = ! empty($this->customFields) ? $this->customFields : ['name' => 'string'];

        $swaggerSchema = '';
        if ($hasSwagger) {
            $swaggerSchema = "/**\n * @OA\\Schema(\n *     schema=\"Store{$name}Request\",\n *     required={".json_encode(array_keys($fields))."},\n";

            foreach ($fields as $field => $type) {
                $phpType = $this->mapSwaggerTypeToPhp($type);
                $example = $this->generateExampleForType($field, $type);
                $swaggerSchema .= " *     @OA\\Property(property=\"{$field}\", type=\"{$phpType}\", example={$example}),\n";
            }

            $swaggerSchema .= " * )\n */\n";
        }

        $rulesArray = [];
        foreach ($fields as $field => $type) {
            $rulesArray[] = "            '{$field}' => ['required', '{$this->mapTypeToValidation($type)}'],";
        }
        $rules = implode("\n", $rulesArray);

        return <<<PHP
<?php
 
namespace App\Http\Requests;
 
use Illuminate\Foundation\Http\FormRequest;
 
{$swaggerSchema}class Store{$name}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
 
    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
{$rules}
        ];
    }
}
PHP;
    }

    // --- Update Request avec Swagger -----------------------------------------

    private function stubUpdateRequest(string $name): string
    {
        $hasSwagger = $this->option('swagger');

        $fields = ! empty($this->customFields) ? $this->customFields : ['name' => 'string'];

        $swaggerSchema = '';
        if ($hasSwagger) {
            $swaggerSchema = "/**\n * @OA\\Schema(\n *     schema=\"Update{$name}Request\",\n";

            foreach ($fields as $field => $type) {
                $phpType = $this->mapSwaggerTypeToPhp($type);
                $example = $this->generateExampleForType($field, $type);
                $swaggerSchema .= " *     @OA\\Property(property=\"{$field}\", type=\"{$phpType}\", example={$example}),\n";
            }

            $swaggerSchema .= " * )\n */\n";
        }

        $rulesArray = [];
        foreach ($fields as $field => $type) {
            $rulesArray[] = "            '{$field}' => ['sometimes', '{$this->mapTypeToValidation($type)}'],";
        }
        $rules = implode("\n", $rulesArray);

        return <<<PHP
<?php
 
namespace App\Http\Requests;
 
use Illuminate\Foundation\Http\FormRequest;
 
{$swaggerSchema}class Update{$name}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
 
    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
{$rules}
        ];
    }
}
PHP;
    }

    // --- Resource avec Swagger -----------------------------------------------

    private function stubResource(string $name): string
    {
        $hasSwagger = $this->option('swagger');

        $fields = ! empty($this->customFields) ? $this->customFields : ['name' => 'string'];

        $swaggerSchema = '';
        if ($hasSwagger) {
            $swaggerSchema = "/**\n * @OA\\Schema(\n *     schema=\"{$name}Resource\",\n";
            $swaggerSchema .= " *     @OA\\Property(property=\"id\", type=\"string\", format=\"uuid\"),\n";

            foreach ($fields as $field => $type) {
                $phpType = $this->mapSwaggerTypeToPhp($type);
                $example = $this->generateExampleForType($field, $type);
                $swaggerSchema .= " *     @OA\\Property(property=\"{$field}\", type=\"{$phpType}\", example={$example}),\n";
            }

            $swaggerSchema .= " *     @OA\\Property(property=\"created_at\", type=\"string\", format=\"date-time\"),\n";
            $swaggerSchema .= " *     @OA\\Property(property=\"updated_at\", type=\"string\", format=\"date-time\")\n";
            $swaggerSchema .= " * )\n */\n";
        }

        $resourceFields = ['\'id\' => $this->id'];
        foreach (array_keys($fields) as $field) {
            $resourceFields[] = "'{$field}' => \$this->{$field}";
        }
        $resourceFields[] = '\'created_at\' => $this->created_at?->toIso8601String()';
        $resourceFields[] = '\'updated_at\' => $this->updated_at?->toIso8601String()';

        $fieldsArray = implode(",\n            ", $resourceFields);

        return <<<PHP
<?php
 
namespace App\Http\Resources;
 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
 
{$swaggerSchema}class {$name}Resource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request \$request): array
    {
        return [
            {$fieldsArray},
        ];
    }
}
PHP;
    }

    // --- Test ----------------------------------------------------------------

    private function stubTest(string $name): string
    {
        $kebab = Str::kebab(Str::plural($name));
        $var = Str::camel($name);

        return <<<PHP
<?php
 
use App\Models\\{$name};
use App\Models\User;
 
use function Pest\Laravel\{actingAs, getJson, postJson, putJson, deleteJson};
 
beforeEach(function () {
    \$this->user = User::factory()->create();
    actingAs(\$this->user);
});
 
describe('{$name}', function () {
 
    it('liste les {$kebab}', function () {
        {$name}::factory()->count(3)->create();
 
        getJson('/api/{$kebab}')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    });
 
    it('crée un {$var}', function () {
        postJson('/api/{$kebab}', ['name' => 'Nouveau {$name}'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nouveau {$name}');
    });
 
    it('affiche un {$var}', function () {
        \${$var} = {$name}::factory()->create();
 
        getJson("/api/{$kebab}/{\${$var}->id}")
            ->assertOk()
            ->assertJsonPath('data.id', \${$var}->id);
    });
 
    it('met à jour un {$var}', function () {
        \${$var} = {$name}::factory()->create();
 
        putJson("/api/{$kebab}/{\${$var}->id}", ['name' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Modifié');
    });
 
    it('supprime un {$var}', function () {
        \${$var} = {$name}::factory()->create();
 
        deleteJson("/api/{$kebab}/{\${$var}->id}")
            ->assertOk()
            ->assertJsonPath('success', true);
 
        \$this->assertDatabaseMissing('{$kebab}', ['id' => \${$var}->id]);
    });
 
    it('retourne 404 pour un {$var} inexistant', function () {
        getJson('/api/{$kebab}/id-inexistant')
            ->assertNotFound();
    });
 
    it('refuse la création sans données requises', function () {
        postJson('/api/{$kebab}', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    });
 
});
PHP;
    }

    // --- Policy --------------------------------------------------------------

    private function stubPolicy(string $name): string
    {
        $var = Str::camel($name);

        return <<<PHP
<?php
 
namespace App\Policies;
 
use App\Models\\{$name};
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
 
class {$name}Policy
{
    use HandlesAuthorization;
 
    private function isOrgAdmin(User \$user): bool
    {
        return \$user->memberships()
            ->where('organization_id', \$user->current_organization_id)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->exists();
    }
 
    public function viewAny(User \$user): bool
    {
        return true;
    }
 
    public function view(User \$user, {$name} \${$var}): bool
    {
        return \${$var}->organization_id === \$user->current_organization_id;
    }
 
    public function create(User \$user): bool
    {
        return \$this->isOrgAdmin(\$user);
    }
 
    public function update(User \$user, {$name} \${$var}): bool
    {
        return \${$var}->organization_id === \$user->current_organization_id
            && \$this->isOrgAdmin(\$user);
    }
 
    public function delete(User \$user, {$name} \${$var}): bool
    {
        return \${$var}->organization_id === \$user->current_organization_id
            && \$this->isOrgAdmin(\$user);
    }
 
    public function restore(User \$user, {$name} \${$var}): bool
    {
        return \$this->isOrgAdmin(\$user);
    }
 
    public function forceDelete(User \$user, {$name} \${$var}): bool
    {
        return false;
    }
}
PHP;
    }

    // --- ApiResponse Trait ---------------------------------------------------

    private function stubApiResponseTrait(): string
    {
        return <<<'PHP'
<?php
 
namespace App\Traits;
 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
 
trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'Opération réussie.',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $payload = ['success' => true, 'message' => $message];
 
        if ($data !== null) {
            $payload['data'] = $data instanceof JsonResource || $data instanceof ResourceCollection
                ? $data->resolve()
                : $data;
        }
 
        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }
 
        return response()->json($payload, $status);
    }
 
    protected function created(mixed $data = null, string $message = 'Ressource créée avec succès.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }
 
    protected function updated(mixed $data = null, string $message = 'Ressource mise à jour avec succès.'): JsonResponse
    {
        return $this->success($data, $message);
    }
 
    protected function deleted(string $message = 'Ressource supprimée avec succès.'): JsonResponse
    {
        return $this->success(null, $message);
    }
 
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
 
    protected function accepted(string $message = 'Requête acceptée, traitement en cours.'): JsonResponse
    {
        return $this->success(null, $message, 202);
    }
 
    protected function paginated(
        LengthAwarePaginator $paginator,
        ?string $resourceClass = null,
        string $message = 'Données récupérées avec succès.'
    ): JsonResponse {
        $items = $resourceClass
            ? $resourceClass::collection($paginator->items())
            : $paginator->items();
 
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $items,
            'meta'    => [
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                    'has_more'     => $paginator->hasMorePages(),
                ],
            ],
        ]);
    }
 
    protected function collection(Collection|array $items, string $message = 'Données récupérées avec succès.'): JsonResponse
    {
        return $this->success($items, $message);
    }
 
    protected function error(
        string $message = 'Une erreur est survenue.',
        int $status = 400,
        ?array $errors = null,
        mixed $debug = null
    ): JsonResponse {
        $payload = ['success' => false, 'message' => $message];
 
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
 
        if ($debug !== null && config('app.debug')) {
            $payload['debug'] = $debug;
        }
 
        return response()->json($payload, $status);
    }
 
    protected function validationError(array $errors = [], string $message = 'Les données fournies sont invalides.'): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], 422);
    }
 
    protected function notFound(string $message = 'Ressource introuvable.'): JsonResponse
    {
        return $this->error($message, 404);
    }
 
    protected function unauthorized(string $message = 'Authentification requise.'): JsonResponse
    {
        return $this->error($message, 401);
    }
 
    protected function forbidden(string $message = "Vous n'êtes pas autorisé à effectuer cette action."): JsonResponse
    {
        return $this->error($message, 403);
    }
 
    protected function conflict(string $message = 'Un conflit est survenu avec la ressource existante.'): JsonResponse
    {
        return $this->error($message, 409);
    }
 
    protected function tooManyRequests(string $message = 'Trop de requêtes. Veuillez réessayer plus tard.'): JsonResponse
    {
        return $this->error($message, 429);
    }
 
    protected function serverError(string $message = 'Erreur interne du serveur.', ?\Throwable $exception = null): JsonResponse
    {
        $debug = null;
 
        if ($exception !== null && config('app.debug')) {
            $debug = [
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                'trace'     => collect($exception->getTrace())->take(5)->toArray(),
            ];
        }
 
        return $this->error($message, 500, null, $debug);
    }
 
    protected function serviceUnavailable(string $message = 'Le service est temporairement indisponible.'): JsonResponse
    {
        return $this->error($message, 503);
    }
 
    protected function successOr(
        bool $condition,
        string $successMessage = 'Opération réussie.',
        string $errorMessage = 'Opération échouée.',
        mixed $data = null
    ): JsonResponse {
        return $condition
            ? $this->success($data, $successMessage)
            : $this->error($errorMessage);
    }
 
    protected function try(callable $callback, string $errorMessage = 'Une erreur inattendue est survenue.'): JsonResponse
    {
        try {
            return $callback();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound();
        } catch (\Illuminate\Auth\Access\AuthorizationException) {
            return $this->forbidden();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Throwable $e) {
            return $this->serverError($errorMessage, $e);
        }
    }
}
PHP;
    }

    // =========================================================================
    // Type Mapping Helpers
    // =========================================================================

    private function mapSwaggerTypeToPhp(string $type): string
    {
        return match (strtolower($type)) {
            'string', 'text' => 'string',
            'integer', 'int' => 'integer',
            'float', 'decimal', 'double' => 'number',
            'boolean', 'bool' => 'boolean',
            'date' => 'string',
            'datetime' => 'string',
            default => 'string',
        };
    }

    private function mapTypeToValidation(string $type): string
    {
        return match (strtolower($type)) {
            'string', 'text' => 'string|max:255',
            'integer', 'int' => 'integer',
            'float', 'decimal', 'double' => 'numeric',
            'boolean', 'bool' => 'boolean',
            'date' => 'date',
            'datetime' => 'date',
            'email' => 'email',
            default => 'string',
        };
    }

    private function generateExampleForType(string $field, string $type): string
    {
        if (str_contains(strtolower($field), 'email')) {
            return '"user@example.com"';
        }

        if (str_contains(strtolower($field), 'phone')) {
            return '"+22890123456"';
        }

        return match (strtolower($type)) {
            'string', 'text' => '"Exemple de '.$field.'"',
            'integer', 'int' => '42',
            'float', 'decimal', 'double' => '99.99',
            'boolean', 'bool' => 'true',
            'date' => '"2025-05-01"',
            'datetime' => '"2025-05-01T10:30:00Z"',
            default => '"'.$field.' value"',
        };
    }
}
