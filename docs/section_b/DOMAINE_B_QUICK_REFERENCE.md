# OrientTogo — Domaine B | QUICK REFERENCE

*Pour commencer MAINTENANT — copier-coller ready*

---

## 🚀 DÉMARRER EN 30 MIN

### 1. Créer la structure

```bash
# Dossiers
mkdir -p app/Services/Domain
mkdir -p app/Repositories/Contracts
mkdir -p app/Repositories/Eloquent
mkdir -p app/Actions/Domain
mkdir -p app/DTOs/Domain
mkdir -p app/Http/Controllers/Api/V1/Domain
mkdir -p app/Http/Requests/Domain
mkdir -p app/Http/Resources/Domain
mkdir -p database/seeders/Domain

# Enum
php artisan make:enum SerieEnum
php artisan make:enum RegionEnum
php artisan make:enum EtablissementTypeEnum
```

### 2. Provider Service

Créer `app/Providers/DomainServiceProvider.php` :

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\SerieRepositoryInterface;
use App\Repositories\Eloquent\SerieRepository;
use App\Services\Domain\SerieService;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(SerieRepositoryInterface::class, SerieRepository::class);
        // ... autres repositories

        // Services
        $this->app->singleton(SerieService::class);
        // ... autres services
    }
}
```

Ajouter dans `config/app.php` :

```php
'providers' => [
    // ...
    App\Providers\DomainServiceProvider::class,
],
```

---

## 📝 TEMPLATE STANDARD

### Migration (copier-coller)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom')->unique();
            $table->string('slug')->unique();
            $table->enum('type', ['lycee', 'universite', 'ecole_privee', 'centre_formation']);
            $table->enum('region', ['maritime', 'plateaux', 'centrale', 'kara', 'savanes']);
            $table->string('ville');
            $table->text('adresse');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('site_web')->nullable();
            $table->text('description');
            $table->string('frais_indicatifs');
            $table->json('specialites')->nullable();
            $table->decimal('coord_latitude', 10, 8)->nullable();
            $table->decimal('coord_longitude', 11, 8)->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->uuid('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('est_selectionne')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indices
            $table->index('region');
            $table->index('type');
            $table->index('is_verified');
            $table->fullText(['nom', 'description', 'ville']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
```

### Model (copier-coller)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

class Etablissement extends Model
{
    use HasUuids, HasSlug, SoftDeletes;

    #[Fillable([
        'nom', 'type', 'region', 'ville', 'adresse', 'contact_email', 
        'contact_phone', 'site_web', 'description', 'frais_indicatifs', 
        'specialites', 'is_public', 'is_verified', 'verification_status',
        'verified_by_user_id', 'verified_at', 'est_selectionne',
        'coord_latitude', 'coord_longitude'
    ])]

    protected $table = 'etablissements';

    protected function casts(): array
    {
        return [
            'specialites'     => 'array',
            'coord_latitude'  => 'float',
            'coord_longitude' => 'float',
            'is_public'       => 'boolean',
            'is_verified'     => 'boolean',
            'est_selectionne' => 'boolean',
            'verified_at'     => 'datetime',
        ];
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeInRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
```

### Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

interface EtablissementRepositoryInterface
{
    public function all(): \Illuminate\Support\Collection;
    public function findById(string $id): ?\App\Models\Etablissement;
    public function findBySlug(string $slug): ?\App\Models\Etablissement;
    public function verified(): \Illuminate\Support\Collection;
    public function inRegion(string $region): \Illuminate\Support\Collection;
    public function search(string $query): \Illuminate\Support\Collection;
    public function create(array $data): \App\Models\Etablissement;
    public function update(string $id, array $data): \App\Models\Etablissement;
    public function delete(string $id): bool;
}
```

### Repository Implementation

```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Etablissement;
use App\Repositories\Contracts\EtablissementRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EtablissementRepository implements EtablissementRepositoryInterface
{
    private const CACHE_TTL = 7200;

    public function all(): Collection
    {
        return Cache::remember('etablissements.all', self::CACHE_TTL, fn() =>
            Etablissement::verified()->get()
        );
    }

    public function findById(string $id): ?Etablissement
    {
        return Cache::remember("etablissement.{$id}", self::CACHE_TTL, fn() =>
            Etablissement::verified()->find($id)
        );
    }

    public function findBySlug(string $slug): ?Etablissement
    {
        return Cache::remember("etablissement.slug.{$slug}", self::CACHE_TTL, fn() =>
            Etablissement::verified()->where('slug', $slug)->first()
        );
    }

    public function verified(): Collection
    {
        return Cache::remember('etablissements.verified', self::CACHE_TTL, fn() =>
            Etablissement::verified()->orderBy('nom')->get()
        );
    }

    public function inRegion(string $region): Collection
    {
        return Cache::remember("etablissements.region.{$region}", self::CACHE_TTL, fn() =>
            Etablissement::verified()->inRegion($region)->get()
        );
    }

    public function search(string $query): Collection
    {
        return Etablissement::verified()
            ->whereRaw("to_tsvector('french', nom || ' ' || ville) @@ plainto_tsquery('french', ?)", [$query])
            ->limit(50)
            ->get();
    }

    public function create(array $data): Etablissement
    {
        $etablissement = Etablissement::create($data);
        $this->invalidateCache();
        return $etablissement;
    }

    public function update(string $id, array $data): Etablissement
    {
        $etablissement = Etablissement::findOrFail($id);
        $etablissement->update($data);
        Cache::forget("etablissement.{$id}");
        $this->invalidateCache();
        return $etablissement;
    }

    public function delete(string $id): bool
    {
        $result = Etablissement::destroy($id);
        Cache::forget("etablissement.{$id}");
        $this->invalidateCache();
        return $result > 0;
    }

    private function invalidateCache(): void
    {
        Cache::forget('etablissements.all');
        Cache::forget('etablissements.verified');
        collect(['maritime', 'plateaux', 'centrale', 'kara', 'savanes'])
            ->each(fn($r) => Cache::forget("etablissements.region.{$r}"));
    }
}
```

### Service

```php
<?php

namespace App\Services\Domain;

use App\Repositories\Contracts\EtablissementRepositoryInterface;
use Illuminate\Support\Collection;

class EtablissementService
{
    public function __construct(
        private EtablissementRepositoryInterface $repository,
    ) {}

    public function findByRegionAndType(string $region, ?string $type = null): Collection
    {
        $items = $this->repository->inRegion($region);
        
        if ($type) {
            $items = $items->filter(fn($e) => $e->type === $type);
        }

        return $items;
    }

    public function search(string $query): Collection
    {
        return $this->repository->search($query);
    }
}
```

### Controller

```php
<?php

namespace App\Http\Controllers\Api\V1\Domain;

use App\Http\Controllers\Controller;
use App\Http\Resources\Domain\EtablissementResource;
use App\Services\Domain\EtablissementService;
use Illuminate\Http\JsonResponse;

class EtablissementController extends Controller
{
    public function __construct(
        private EtablissementService $service,
    ) {}

    public function index(): JsonResponse
    {
        $region = request('region');
        $type = request('type');
        $search = request('search');

        if ($search) {
            $etablissements = $this->service->search($search);
        } else {
            $etablissements = $this->service->findByRegionAndType($region, $type);
        }

        return response()->json([
            'data' => EtablissementResource::collection($etablissements),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $etablissement = $this->service->findBySlug($slug);

        if (!$etablissement) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'data' => new EtablissementResource($etablissement),
        ]);
    }
}
```

### Resource

```php
<?php

namespace App\Http\Resources\Domain;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtablissementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'nom'              => $this->nom,
            'slug'             => $this->slug,
            'type'             => $this->type,
            'region'           => $this->region,
            'ville'            => $this->ville,
            'adresse'          => $this->adresse,
            'contact_email'    => $this->contact_email,
            'contact_phone'    => $this->contact_phone,
            'site_web'         => $this->site_web,
            'description'      => $this->description,
            'frais_indicatifs' => $this->frais_indicatifs,
            'specialites'      => $this->specialites,
            'coord'            => [
                'latitude'  => $this->coord_latitude,
                'longitude' => $this->coord_longitude,
            ],
        ];
    }
}
```

### Routes (dans `routes/api.php`)

```php
Route::prefix('v1')->group(function () {
    // PUBLIC
    Route::get('/etablissements', [EtablissementController::class, 'index']);
    Route::get('/etablissements/{slug}', [EtablissementController::class, 'show']);

    // ADMIN
    Route::middleware(['auth:sanctum', 'role:editor|admin'])->group(function () {
        Route::post('/etablissements', [EtablissementController::class, 'store']);
        Route::patch('/etablissements/{id}', [EtablissementController::class, 'update']);
        Route::delete('/etablissements/{id}', [EtablissementController::class, 'destroy']);
    });
});
```

### Test (Pest)

```php
<?php

namespace Tests\Feature\Domain;

use App\Models\Etablissement;
use Tests\TestCase;

class EtablissementTest extends TestCase
{
    public function test_get_etablissements(): void
    {
        Etablissement::factory()->create(['is_verified' => true]);

        $response = $this->getJson('/api/v1/etablissements');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'nom', 'region']]]);
    }

    public function test_search_etablissements(): void
    {
        Etablissement::factory()->create(['nom' => 'Lycée de Tokoin', 'is_verified' => true]);

        $response = $this->getJson('/api/v1/etablissements?search=tokoin');

        $response->assertStatus(200);
    }
}
```

---

## 🛠️ COMMANDES UTILES

```bash
# Créer model + migration
php artisan make:model Etablissement -m

# Créer repository
php artisan make:class Repositories/Eloquent/EtablissementRepository
php artisan make:class Repositories/Contracts/EtablissementRepositoryInterface

# Créer service
php artisan make:class Services/Domain/EtablissementService

# Créer controller API
php artisan make:controller Api/V1/Domain/EtablissementController --api

# Créer form request
php artisan make:request Domain/EtablissementRequest

# Créer resource
php artisan make:resource Domain/EtablissementResource

# Migrations
php artisan migrate
php artisan migrate:refresh --seed
php artisan migrate:fresh --seed

# Tests
php artisan test
php artisan test tests/Feature/Domain/EtablissementTest.php
php artisan test --parallel

# Database
php artisan tinker
>>> Etablissement::count()
>>> Etablissement::with('formations')->first()
```

---

## 🔍 CHECKLIST QUOTIDIENNE

**Chaque jour :**
- [ ] `php artisan test` passe 100%
- [ ] Pas de `dd()` ou `var_dump()`
- [ ] PSR-12 format : `php-cs-fixer fix app/`
- [ ] Migrations appliquées
- [ ] Cache invalidé quand modèles changent

**Avant commit :**
- [ ] Tests passent
- [ ] Aucun warning
- [ ] Code propre
- [ ] Comment utile (pas évidentes)
- [ ] Pas de secrets en dur

---

## 📊 DONNÉES DE TEST

### Factory pour tests

```php
Etablissement::factory()
    ->count(10)
    ->create([
        'region' => 'maritime',
        'type' => 'lycee',
        'is_verified' => true,
    ]);
```

### Seeder simple

```php
<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use Illuminate\Database\Seeder;

class EtablissementSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nom' => 'Lycée de Tokoin', 'type' => 'lycee', 'region' => 'maritime'],
            ['nom' => 'Lycée de Bè', 'type' => 'lycee', 'region' => 'maritime'],
        ];

        Etablissement::insert($data);
    }
}
```

---

## 🆘 PROBLÈMES COURANTS & SOLUTIONS

| Problème | Solution |
|----------|----------|
| `Class not found` | `composer dump-autoload` |
| Migration fails | Vérifier syntax, vérifier migrations précédentes |
| Cache stale | `php artisan cache:clear` |
| N+1 queries | Ajouter `.load('relations')` dans repository |
| SearchException | Vérifier fullText index, PostgreSQL version |
| 401 Unauthorized | Vérifier token Sanctum, vérifier middleware |
| 422 Validation error | Vérifier Form Request rules |
| Soft delete issues | Vérifier `withoutTrashed()`, `onlyTrashed()` |

---

## 📚 RESSOURCES

- **Laravel Docs** : https://laravel.com/docs
- **Eloquent** : https://laravel.com/docs/eloquent
- **Sanctum** : https://laravel.com/docs/sanctum
- **Pest** : https://pestphp.com
- **PostgreSQL** : https://www.postgresql.org/docs/

---

*Quick Reference v1.0 — Copier-coller ready*
