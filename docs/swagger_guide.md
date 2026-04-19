# Guide d'intégration Swagger dans Laravel 13

## Table des matières
1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Annotations de base](#annotations-de-base)
4. [Exemples pratiques](#exemples-pratiques)
5. [Génération de la documentation](#génération-de-la-documentation)
6. [Bonnes pratiques](#bonnes-pratiques)

---

## Installation

### Étape 1 : Installer le package

```bash
composer require darkaonline/l5-swagger
```

### Étape 2 : Publier la configuration

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

Cela crée le fichier `config/l5-swagger.php` et les assets nécessaires.

---

## Configuration

### Fichier `config/l5-swagger.php`

Voici les configurations essentielles à ajuster :

```php
<?php

return [
    'default' => 'default',
    
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Mon API Laravel 13',
            ],
            
            'routes' => [
                'api' => 'api/documentation',  // URL d'accès à la doc
            ],
            
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                
                'annotations' => [
                    base_path('app/Http/Controllers'),
                    base_path('app/Http/Requests'),
                    base_path('app/Http/Resources'),
                    base_path('app/Models'),
                ],
            ],
        ],
    ],
    
    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
        ],
        
        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
            'excludes' => [],
        ],
        
        'scanOptions' => [
            'analyser' => null,
            'analysis' => null,
            'processors' => [],
            'pattern' => null,
            'exclude' => [],
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION),
        ],
        
        'securityDefinitions' => [
            'securitySchemes' => [
                'sanctum' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Entrez votre token Bearer (obtenu via /api/login)',
                ],
            ],
            'security' => [
                ['sanctum' => []],
            ],
        ],
        
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'validator_url' => null,
        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],
            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),
            ],
        ],
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost'),
        ],
    ],
];
```

### Variables d'environnement `.env`

```env
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=http://localhost:8000
L5_SWAGGER_USE_ABSOLUTE_PATH=true
```

---

## Annotations de base

### 1. Configuration globale de l'API

Créez un fichier `app/Http/Controllers/Controller.php` (ou modifiez-le) :

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Mon API Laravel 13",
 *     description="Documentation complète de l'API",
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
 *     description="Authentification via Laravel Sanctum. Entrez le token Bearer obtenu lors de la connexion."
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints d'authentification"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
```

### 2. Annoter un Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Invoices",
 *     description="Gestion des factures"
 * )
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/invoices",
     *     summary="Liste des factures",
     *     description="Retourne une liste paginée de toutes les factures avec filtres optionnels",
     *     operationId="getInvoices",
     *     tags={"Invoices"},
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
     *             @OA\Property(property="message", type="string", example="Données récupérées avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/InvoiceResource")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=7),
     *                     @OA\Property(property="from", type="integer", example=1),
     *                     @OA\Property(property="to", type="integer", example=15),
     *                     @OA\Property(property="has_more", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return $this->service->index($request);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/{id}",
     *     summary="Détails d'une facture",
     *     description="Retourne les détails d'une facture spécifique",
     *     operationId="getInvoice",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la facture (UUID)",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facture trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoice récupéré."),
     *             @OA\Property(property="data", ref="#/components/schemas/InvoiceResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facture non trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return $this->service->show($invoice);
    }

    /**
     * @OA\Post(
     *     path="/api/invoices",
     *     summary="Créer une facture",
     *     description="Crée une nouvelle facture",
     *     operationId="createInvoice",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreInvoiceRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Facture créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ressource créée avec succès."),
     *             @OA\Property(property="data", ref="#/components/schemas/InvoiceResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/invoices/{id}",
     *     summary="Mettre à jour une facture",
     *     description="Met à jour une facture existante",
     *     operationId="updateInvoice",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la facture",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateInvoiceRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facture mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ressource mise à jour avec succès."),
     *             @OA\Property(property="data", ref="#/components/schemas/InvoiceResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facture non trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        return $this->service->update($invoice, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/invoices/{id}",
     *     summary="Supprimer une facture",
     *     description="Supprime une facture",
     *     operationId="deleteInvoice",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la facture",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facture supprimée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ressource supprimée avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facture non trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        return $this->service->destroy($invoice);
    }
}
```

### 3. Annoter les Form Requests

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreInvoiceRequest",
 *     required={"name", "amount", "due_date"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="Facture #001"),
 *     @OA\Property(property="amount", type="number", format="float", example=1500.50),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-05-01"),
 *     @OA\Property(property="description", type="string", example="Description de la facture")
 * )
 */
class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateInvoiceRequest",
 *     @OA\Property(property="name", type="string", maxLength=255, example="Facture modifiée"),
 *     @OA\Property(property="amount", type="number", format="float", example=2000.00),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-06-01"),
 *     @OA\Property(property="description", type="string", example="Nouvelle description")
 * )
 */
class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'due_date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
```

### 4. Annoter les Resources

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="InvoiceResource",
 *     @OA\Property(property="id", type="string", format="uuid", example="9d8e4f2a-1b3c-4d5e-6f7a-8b9c0d1e2f3a"),
 *     @OA\Property(property="name", type="string", example="Facture #001"),
 *     @OA\Property(property="amount", type="number", format="float", example=1500.50),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-05-01"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Description"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-17T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-17T14:20:00Z")
 * )
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'amount'      => $this->amount,
            'due_date'    => $this->due_date,
            'description' => $this->description,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### 5. Schémas de réponses d'erreur (à ajouter dans Controller.php)

```php
/**
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
 *             property="name",
 *             type="array",
 *             @OA\Items(type="string", example="Le champ nom est obligatoire.")
 *         )
 *     )
 * )
 */
```

---

## Génération de la documentation

### Commande de génération

```bash
php artisan l5-swagger:generate
```

Cette commande scanne vos annotations et génère les fichiers JSON/YAML de la documentation.

### Accéder à la documentation

Démarrez votre serveur :

```bash
php artisan serve
```

Accédez à l'URL :

```
http://localhost:8000/api/documentation
```

### Automatiser la génération

Pour régénérer automatiquement la doc à chaque requête (en développement) :

Dans `.env` :

```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

**⚠️ En production, mettez cette valeur à `false` et générez manuellement.**

---

## Exemples pratiques

### Exemple 1 : Endpoint avec plusieurs réponses possibles

```php
/**
 * @OA\Post(
 *     path="/api/auth/login",
 *     summary="Connexion utilisateur",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Connexion réussie",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Connexion réussie"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="token", type="string", example="1|abc123..."),
 *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Identifiants invalides",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation échouée",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
public function login(LoginRequest $request): JsonResponse
{
    // ...
}
```

### Exemple 2 : Upload de fichier

```php
/**
 * @OA\Post(
 *     path="/api/documents",
 *     summary="Upload d'un document",
 *     tags={"Documents"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"file", "title"},
 *                 @OA\Property(property="title", type="string", example="Mon document"),
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary",
 *                     description="Fichier PDF, DOCX ou image"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Document uploadé",
 *         @OA\JsonContent(ref="#/components/schemas/DocumentResource")
 *     )
 * )
 */
public function store(StoreDocumentRequest $request): JsonResponse
{
    // ...
}
```

### Exemple 3 : Paramètres de requête multiples

```php
/**
 * @OA\Get(
 *     path="/api/products",
 *     summary="Liste des produits avec filtres avancés",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="category",
 *         in="query",
 *         description="Filtrer par catégorie",
 *         @OA\Schema(type="string", enum={"electronics", "clothing", "food"})
 *     ),
 *     @OA\Parameter(
 *         name="min_price",
 *         in="query",
 *         description="Prix minimum",
 *         @OA\Schema(type="number", format="float", minimum=0)
 *     ),
 *     @OA\Parameter(
 *         name="max_price",
 *         in="query",
 *         description="Prix maximum",
 *         @OA\Schema(type="number", format="float", minimum=0)
 *     ),
 *     @OA\Parameter(
 *         name="in_stock",
 *         in="query",
 *         description="Filtrer les produits en stock",
 *         @OA\Schema(type="boolean")
 *     ),
 *     @OA\Response(response=200, description="Liste des produits")
 * )
 */
public function index(Request $request): JsonResponse
{
    // ...
}
```

---

## Bonnes pratiques

### 1. Organisation des annotations

- **Controller.php** : Configuration globale (info, servers, security)
- **Controllers** : Documentation des endpoints
- **Requests** : Schémas de validation
- **Resources** : Structure des réponses
- **Models** (optionnel) : Schémas de données

### 2. Utilisation des `ref`

Réutilisez les schémas avec `ref` :

```php
@OA\JsonContent(ref="#/components/schemas/InvoiceResource")
```

Au lieu de redéfinir toute la structure.

### 3. Tags cohérents

Groupez vos endpoints par ressource :

```php
tags={"Invoices"}
tags={"Authentication"}
tags={"Users"}
```

### 4. Descriptions claires

```php
/**
 * @OA\Get(
 *     summary="Courte description (50 chars max)",
 *     description="Description détaillée qui peut être plus longue et expliquer 
 *                  le comportement de l'endpoint, les cas particuliers, etc."
 * )
 */
```

### 5. Exemples réalistes

Ajoutez des exemples concrets dans vos schémas :

```php
@OA\Property(property="email", type="string", example="john.doe@example.com")
```

### 6. Documentation des erreurs

Documentez **toutes** les réponses possibles (200, 201, 400, 401, 403, 404, 422, 500).

### 7. Sécurité

N'oubliez pas d'ajouter `security={{"sanctum":{}}}` sur les routes protégées.

### 8. Versioning

Si vous versionnez votre API :

```php
/**
 * @OA\Info(version="2.0.0")
 */
```

### 9. Environnements

En production, désactivez la génération automatique :

```env
# .env.production
L5_SWAGGER_GENERATE_ALWAYS=false
```

Et exécutez manuellement :

```bash
php artisan l5-swagger:generate
```

### 10. Cache

Swagger met en cache la documentation. Pour forcer la regénération :

```bash
php artisan config:clear
php artisan cache:clear
php artisan l5-swagger:generate
```

---

## Commandes utiles

```bash
# Générer la documentation
php artisan l5-swagger:generate

# Publier la configuration
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

# Vider le cache
php artisan config:clear
php artisan cache:clear

# Vérifier les annotations (via OpenAPI)
vendor/bin/openapi app/Http/Controllers
```

---

## Dépannage

### Problème : La documentation ne se génère pas

**Solution** :
1. Vérifiez que les chemins dans `config/l5-swagger.php` sont corrects
2. Assurez-vous que les annotations sont syntaxiquement correctes
3. Vérifiez les permissions sur `storage/api-docs/`

```bash
chmod -R 775 storage/api-docs/
```

### Problème : Erreur 404 sur `/api/documentation`

**Solution** :
1. Vérifiez la route dans `config/l5-swagger.php`
2. Effacez le cache de configuration :

```bash
php artisan config:clear
php artisan route:clear
```

### Problème : Authentification ne fonctionne pas

**Solution** :
1. Vérifiez la configuration `securityScheme` dans `config/l5-swagger.php`
2. Dans l'UI Swagger, cliquez sur "Authorize" et entrez : `Bearer VOTRE_TOKEN`

---

## Ressources

- [Documentation officielle L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger)
- [Spécification OpenAPI 3.0](https://swagger.io/specification/)
- [Annotations PHP OpenAPI](https://zircote.github.io/swagger-php/)

---

**Votre API est maintenant documentée avec Swagger ! 🎉**

Accédez à : `http://localhost:8000/api/documentation`
