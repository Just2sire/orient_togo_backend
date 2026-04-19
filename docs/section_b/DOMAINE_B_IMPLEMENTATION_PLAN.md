# OrientTogo — Domaine B | Plan d'Implémentation Détaillé
## Contenu Éducatif (Séries, Établissements, Formations, Métiers, Secteurs)

*Document version 1.0 — Laravel 13 · PHP 8.3 · Pest · Repository Pattern*

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'ensemble du Domaine B](#1-vue-densemble)
2. [Modèles à créer / compléter](#2-modèles)
3. [Repositories](#3-repositories)
4. [Services métier](#4-services)
5. [Actions atomiques](#5-actions)
6. [Form Requests](#6-form-requests)
7. [Controllers API](#7-controllers)
8. [Resources API](#8-resources)
9. [Routes API](#9-routes)
10. [Seeding des données](#10-seeding)
11. [Tests (Pest)](#11-tests)
12. [Checklist finale](#12-checklist)

---

## 1. VUE D'ENSEMBLE

### Qu'est-ce que le Domaine B ?

Le Domaine B gère **tout le contenu éducatif** de l'application :
- **Séries** (A, C, D, F, G) → critères d'accès, débouchés, moyennes requises
- **Établissements** (lycées, universités, écoles privées, centres de formation)
- **Formations** (diplômes, cursus) proposées par les établissements
- **Métiers** (fiches métier, débouchés, salaires, chemins de formation)
- **Secteurs porteurs** (tendances du marché, données salariales)
- **Matières & Coefficients** (pour les calculs de moyenne BEPC/BAC)

### Architecture

```
Entités du Domaine B
├── Serie (A, C, D, F1-F4, G1-G3)
│   └── MatiereCoefficient (la matière "Maths" avec coef 7 pour série C)
│
├── Etablissement (lycées, universités, écoles, centres)
│   ├── Formation (licence, BTS, etc. proposés par cet établissement)
│   └── Media (photos, logo)
│
├── Metier (professions individuelles)
│   ├── Filiere (le cursus pour y arriver)
│   └── DonneeSectorielle (salaire moyen, débouchés)
│
├── SecteurPorteur (domaines qui recrutent : Tech, Santé, etc.)
│   └── DonneeSectorielle (stats agrégées par secteur)
│
└── Structures transversales
    ├── Filiere (définit les cursus possibles)
    └── Tag (taxonomie pour filtrage : "agro", "tech", "sante")
```

### Périmètre ÉTAPE 1 (ce plan)

**À faire :**
- ✅ Modèles Eloquent complets
- ✅ Repositories + Interfaces
- ✅ Services de gestion de contenu
- ✅ Actions atomiques
- ✅ Form Requests pour admin
- ✅ Controllers CRUD (read public, write admin)
- ✅ Resources API
- ✅ Routes API versionnées
- ✅ Seeding données Togo
- ✅ Tests Pest (Unit + Feature)

**Hors périmètre :**
- ❌ Quiz (Domaine C)
- ❌ Notifications (Domaine F)
- ❌ Dashboard admin avancé (Domaine D)

---

## 2. MODÈLES

### 2.1 — Serie

**Responsabilité :** Définir les 7 séries du lycée togolais avec leurs critères d'accès.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

class Serie extends Model
{
    use HasUuids, HasTimestamps;

    #[Fillable(['code', 'label', 'description', 'moyenne_minimale', 'profil_requis', 'apres_bac', 'tips_amelioration', 'is_active', 'ordre'])]

    protected $table = 'series';
    public $timestamps = true;

    // Colonnes dans la migration:
    // - code: string(2) unique  → 'A', 'C', 'D', 'F1', 'F2', 'F3', 'F4', 'G1', 'G2', 'G3'
    // - label: string          → 'Lettres et SH', 'Maths-PC', 'SVT', etc.
    // - description: text      → Explicatif long (peut être en Markdown)
    // - moyenne_minimale: decimal(3,2) → ex: 10.50
    // - profil_requis: text    → "Fort en français, histoire-géo..."
    // - apres_bac: text        → "Droit, Journalisme, SHS..."
    // - tips_amelioration: jsonb → {"math": "Augmente de +2", "francais": "Déjà bon"}
    // - is_active: boolean DEFAULT true
    // - ordre: smallint        → Pour trier A avant C avant D
    // - created_at, updated_at

    // Relations
    public function matieres(): HasMany
    {
        return $this->hasMany(MatiereCoefficient::class, 'serie_id');
    }

    public function filieres(): BelongsToMany
    {
        return $this->belongsToMany(
            Filiere::class,
            table: 'serie_filiere',
            foreignPivotKey: 'serie_id',
            relatedPivotKey: 'filiere_id'
        );
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }
}
```

### 2.2 — MatiereCoefficient

**Responsabilité :** Lier une matière à une série avec son coefficient.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatiereCoefficient extends Model
{
    use HasUuids;

    public $timestamps = false;  // Cette table ne change presque jamais
    #[Fillable(['serie_id', 'matiere_nom', 'coefficient', 'note_minimale'])]

    protected $table = 'matiere_coefficients';

    // Colonnes:
    // - serie_id: uuid FK
    // - matiere_nom: string  → 'Mathématiques', 'Français', 'Physique-Chimie'...
    // - coefficient: smallint → 4, 7, 3, etc.
    // - note_minimale: decimal(3,2) NULLABLE → ex: 8.0 (peut être NULL si pas d'exigence)
    // - created_at (created_by_user_id qui a défini ça)

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }
}
```

### 2.3 — Etablissement

**Responsabilité :** Représenter un établissement (lycée, université, école privée, centre de formation).

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;

class Etablissement extends Model implements HasMedia
{
    use HasUuids, HasTimestamps, SoftDeletes, HasSlug, InteractsWithMedia;

    #[Fillable([
        'nom', 'type', 'region', 'ville', 'adresse', 'contact_email', 'contact_phone',
        'site_web', 'description', 'frais_indicatifs', 'specialites', 'is_public', 'is_verified',
        'verification_status', 'verified_by_user_id', 'verified_at', 'est_selectionne',
        'coord_latitude', 'coord_longitude'
    ])]

    protected $table = 'etablissements';
    protected $dates = ['deleted_at', 'verified_at'];

    protected function casts(): array
    {
        return [
            'specialites'     => 'array',  // JSON: ['Médecine', 'Pharmacie']
            'coord_latitude'  => 'float',
            'coord_longitude' => 'float',
            'is_public'       => 'boolean',
            'is_verified'     => 'boolean',
            'est_selectionne' => 'boolean',
        ];
    }

    // Colonnes:
    // - nom: string UNIQUE
    // - type: enum → 'lycee', 'universite', 'ecole_privee', 'centre_formation'
    // - region: enum → 'maritime', 'plateaux', 'centrale', 'kara', 'savanes'
    // - ville: string
    // - adresse: text
    // - contact_email, contact_phone: string NULLABLE
    // - site_web: string NULLABLE
    // - description: text
    // - frais_indicatifs: varchar (ex: "10.000 - 50.000 FCFA")
    // - specialites: jsonb ['Médecine', 'Droit', ...]
    // - is_public: boolean (public vs privé agréé)
    // - is_verified: boolean (contenu validé ou pas)
    // - verification_status: enum 'pending', 'approved', 'rejected'
    // - verified_by_user_id: uuid NULLABLE (qui l'a validé)
    // - verified_at: timestamp NULLABLE
    // - est_selectionne: boolean (pour la phase MVP, seulement 50 établissements)
    // - coord_latitude, coord_longitude: float (pour la carte interactive)
    // - slug: string UNIQUE (pour les URLs)
    // - created_at, updated_at, deleted_at

    // Relations
    public function formations(): HasMany
    {
        return $this->hasMany(Formation::class, 'etablissement_id');
    }

    public function filieres(): BelongsToMany
    {
        return $this->belongsToMany(
            Filiere::class,
            table: 'etablissement_filiere',
            foreignPivotKey: 'etablissement_id',
            relatedPivotKey: 'filiere_id'
        );
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(
            Serie::class,
            table: 'etablissement_serie',
            foreignPivotKey: 'etablissement_id',
            relatedPivotKey: 'serie_id'
        );
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            table: 'taggable',
            foreignPivotKey: 'taggable_id',
            relatedPivotKey: 'tag_id'
        )->where('taggable_type', self::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

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

    public function scopeSelectif($query)
    {
        return $query->where('est_selectionne', true);
    }

    // Mutators
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value,
            set: fn($value) => Str::slug($this->nom),
        );
    }
}
```

### 2.4 — Formation

**Responsabilité :** Représenter un diplôme/cursus spécifique proposé par un établissement.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};

class Formation extends Model
{
    use HasUuids, HasTimestamps;

    #[Fillable(['etablissement_id', 'nom', 'niveau', 'description', 'duree_mois', 'frais_annual', 'accreditation'])]

    protected $table = 'formations';

    // Colonnes:
    // - etablissement_id: uuid FK
    // - nom: string → "Licence Informatique", "BTS Comptabilité", "Médecine (6 ans)"
    // - niveau: enum → 'bts', 'dut', 'licence', 'master', 'doctorat', 'formation_pro'
    // - description: text
    // - duree_mois: smallint
    // - frais_annual: decimal (NULL si gratuit = public)
    // - accreditation: string NULLABLE → "Accréditation MESRES 2024"
    // - created_at, updated_at

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function filieres(): BelongsToMany
    {
        return $this->belongsToMany(
            Filiere::class,
            table: 'formation_filiere',
            foreignPivotKey: 'formation_id',
            relatedPivotKey: 'filiere_id'
        );
    }

    public function metiers(): BelongsToMany
    {
        return $this->belongsToMany(
            Metier::class,
            table: 'formation_metier',
            foreignPivotKey: 'formation_id',
            relatedPivotKey: 'metier_id'
        );
    }
}
```

### 2.5 — Metier

**Responsabilité :** Définir une profession avec ses débouchés, formations, données salariales.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations::{HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class Metier extends Model
{
    use HasUuids, HasTimestamps, SoftDeletes;

    #[Fillable(['nom', 'description', 'competences_requises', 'demande_marche', 'salaire_min', 'salaire_max', 'description_longue', 'est_porteur'])]

    protected $table = 'metiers';
    protected $dates = ['deleted_at'];

    protected function casts(): array
    {
        return [
            'competences_requises' => 'array',  // ['Programmation', 'Anglais', 'Leadership']
            'demande_marche'       => 'integer', // 1-10 (10 = très porteur)
            'salaire_min'          => 'integer',
            'salaire_max'          => 'integer',
            'est_porteur'          => 'boolean',
        ];
    }

    // Colonnes:
    // - nom: string UNIQUE
    // - description: text (courte, <500 chars)
    // - competences_requises: jsonb ['Programmation', 'Gestion projet']
    // - demande_marche: tinyint (1-10)
    // - salaire_min, salaire_max: integer (en FCFA)
    // - description_longue: text (peut être Markdown)
    // - est_porteur: boolean → Marquer les métiers d'avenir
    // - created_at, updated_at, deleted_at

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(
            Formation::class,
            table: 'formation_metier',
            foreignPivotKey: 'metier_id',
            relatedPivotKey: 'formation_id'
        );
    }

    public function filieres(): BelongsToMany
    {
        return $this->belongsToMany(
            Filiere::class,
            table: 'filiere_metier',
            foreignPivotKey: 'metier_id',
            relatedPivotKey: 'filiere_id'
        );
    }

    public function donneeSectorielle(): HasMany
    {
        return $this->hasMany(DonneeSectorielle::class, 'metier_id');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(SecteurPorteur::class, 'secteur_porteur_id');
    }

    // Scopes
    public function scopePorteur($query)
    {
        return $query->where('est_porteur', true);
    }

    public function scopeEnDemande($query)
    {
        return $query->where('demande_marche', '>=', 7);
    }
}
```

### 2.6 — SecteurPorteur

**Responsabilité :** Grouper les métiers par secteur économique (Tech, Santé, Droit, etc.).

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations::{HasMany, BelongsToMany};

class SecteurPorteur extends Model
{
    use HasUuids, HasTimestamps;

    #[Fillable(['nom', 'description', 'couleur_hex', 'icone_code', 'croissance_annuelle', 'description_opportunites'])]

    protected $table = 'secteurs_porteurs';

    // Colonnes:
    // - nom: string UNIQUE → 'Technologie', 'Santé', 'Agriculture', 'Énergie', 'Finance'
    // - description: text
    // - couleur_hex: string (pour le front) → '#00A86B'
    // - icone_code: string (icon name) → 'computer', 'stethoscope', 'leaf'
    // - croissance_annuelle: decimal(4,2) → 3.5 (%)
    // - description_opportunites: text (Markdown)
    // - created_at, updated_at

    public function metiers(): HasMany
    {
        return $this->hasMany(Metier::class, 'secteur_porteur_id');
    }

    public function donnees(): HasMany
    {
        return $this->hasMany(DonneeSectorielle::class, 'secteur_porteur_id');
    }

    public function filieres(): BelongsToMany
    {
        return $this->belongsToMany(
            Filiere::class,
            table: 'secteur_filiere',
            foreignPivotKey: 'secteur_porteur_id',
            relatedPivotKey: 'filiere_id'
        );
    }
}
```

### 2.7 — DonneeSectorielle

**Responsabilité :** Stocker les données salariales, de croissance, ou statistiques temporelles.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations::BelongsTo;

class DonneeSectorielle extends Model
{
    use HasUuids, HasTimestamps;

    #[Fillable(['metier_id', 'secteur_porteur_id', 'annee', 'salaire_moyen', 'taux_emploi', 'offres_par_an', 'notes'])]

    protected $table = 'donnees_sectorielles';

    // Colonnes:
    // - metier_id: uuid FK NULLABLE
    // - secteur_porteur_id: uuid FK NULLABLE
    // - annee: year (2024, 2025...)
    // - salaire_moyen: integer (FCFA)
    // - taux_emploi: decimal(4,2) (%)
    // - offres_par_an: integer (# offres estimées)
    // - notes: text (source de la donnée, etc.)
    // - created_at, updated_at

    public function metier(): BelongsTo
    {
        return $this->belongsTo(Metier::class);
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(SecteurPorteur::class, 'secteur_porteur_id');
    }
}
```

### 2.8 — Filiere

**Responsabilité :** Représenter un chemin/cursus possible (distinction importante : différent d'une Formation).

**Exemple :** Une "Filière Médecine" regroupe :
  - Les séries d'accès : D, C (mais pas A)
  - Les formations : Licence Médecine (3 ans) → Master Médecine (2 ans) → Doctorat (3 ans)
  - Les métiers débouchés : Médecin, Chirurgien, Généraliste, etc.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations::BelongsToMany;

class Filiere extends Model
{
    use HasUuids, HasTimestamps;

    #[Fillable(['nom', 'description', 'duree_estimee_ans', 'domaine_principal', 'is_selectionne'])]

    protected $table = 'filieres';

    // Colonnes:
    // - nom: string UNIQUE → 'Médecine', 'Informatique', 'Droit', 'Agronomie'
    // - description: text
    // - duree_estimee_ans: decimal(4,1) → 6.5 (études + stage)
    // - domaine_principal: enum 'sante', 'ingenierie', 'droit', 'commerce', 'agriculture', 'arts'
    // - is_selectionne: boolean (pour limiter à 30-50 filières principales)
    // - created_at, updated_at

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(
            Serie::class,
            table: 'serie_filiere',
            foreignPivotKey: 'filiere_id',
            relatedPivotKey: 'serie_id'
        );
    }

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(
            Formation::class,
            table: 'formation_filiere',
            foreignPivotKey: 'filiere_id',
            relatedPivotKey: 'formation_id'
        );
    }

    public function metiers(): BelongsToMany
    {
        return $this->belongsToMany(
            Metier::class,
            table: 'filiere_metier',
            foreignPivotKey: 'filiere_id',
            relatedPivotKey: 'metier_id'
        );
    }

    public function etablissements(): BelongsToMany
    {
        return $this->belongsToMany(
            Etablissement::class,
            table: 'etablissement_filiere',
            foreignPivotKey: 'filiere_id',
            relatedPivotKey: 'etablissement_id'
        );
    }

    public function secteurs(): BelongsToMany
    {
        return $this->belongsToMany(
            SecteurPorteur::class,
            table: 'secteur_filiere',
            foreignPivotKey: 'filiere_id',
            relatedPivotKey: 'secteur_porteur_id'
        );
    }
}
```

### 2.9 — Tag

**Responsabilité :** Taxonomie pour le filtrage (ex: "agriculture", "tech", "sante").

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations::MorphToMany;
use Spatie\Sluggable\HasSlug;

class Tag extends Model
{
    use HasUuids, HasTimestamps, HasSlug;

    #[Fillable(['label', 'category', 'description'])]

    protected $table = 'tags';

    // Colonnes:
    // - label: string → 'Informatique', 'Médecine', 'Agro'
    // - slug: string UNIQUE (généré automatiquement)
    // - category: string → 'domaine', 'secteur', 'specialite' (pour les filtres)
    // - description: string NULLABLE
    // - created_at, updated_at

    // Relation polymorphe
    public function etablissements(): MorphToMany
    {
        return $this->morphedByMany(Etablissement::class, 'taggable', 'taggable');
    }

    public function filieres(): MorphToMany
    {
        return $this->morphedByMany(Filiere::class, 'taggable', 'taggable');
    }

    public function metiers(): MorphToMany
    {
        return $this->morphedByMany(Metier::class, 'taggable', 'taggable');
    }
}
```

---

## 3. REPOSITORIES

### 3.1 — Interfaces (Contracts)

Placer dans `app/Repositories/Contracts/`

```php
// SerieRepositoryInterface.php
namespace App\Repositories\Contracts;

interface SerieRepositoryInterface
{
    public function all(): Collection;
    public function findById(string $id): ?Serie;
    public function findByCode(string $code): ?Serie;
    public function actif(): Collection;
    public function create(array $data): Serie;
    public function update(string $id, array $data): Serie;
    public function delete(string $id): bool;
}

// EtablissementRepositoryInterface.php
namespace App\Repositories\Contracts;

interface EtablissementRepositoryInterface
{
    public function all(): Collection;
    public function paginated(int $perPage = 15): LengthAwarePaginator;
    public function findById(string $id): ?Etablissement;
    public function findBySlug(string $slug): ?Etablissement;
    public function verified(): Collection;
    public function inRegion(string $region): Collection;
    public function ofType(string $type): Collection;
    public function selectif(): Collection;
    public function search(string $query): Collection;
    public function create(array $data): Etablissement;
    public function update(string $id, array $data): Etablissement;
    public function delete(string $id): bool;
    public function verify(string $id, User $verifier): bool;
}

// FormationRepositoryInterface.php
interface FormationRepositoryInterface
{
    public function byEtablissement(string $etablissementId): Collection;
    public function byNiveau(string $niveau): Collection;
    public function create(array $data): Formation;
    public function update(string $id, array $data): Formation;
    public function delete(string $id): bool;
}

// MetierRepositoryInterface.php
interface MetierRepositoryInterface
{
    public function all(): Collection;
    public function porteur(): Collection;
    public function enDemande(): Collection;
    public function findById(string $id): ?Metier;
    public function search(string $query): Collection;
    public function create(array $data): Metier;
    public function update(string $id, array $data): Metier;
    public function delete(string $id): bool;
}

// SecteurPorteurRepositoryInterface.php
interface SecteurPorteurRepositoryInterface
{
    public function all(): Collection;
    public function findById(string $id): ?SecteurPorteur;
    public function withMetiers(string $id): SecteurPorteur;
    public function create(array $data): SecteurPorteur;
    public function update(string $id, array $data): SecteurPorteur;
    public function delete(string $id): bool;
}

// FiliereRepositoryInterface.php
interface FiliereRepositoryInterface
{
    public function all(): Collection;
    public function selectif(): Collection;
    public function byDomaine(string $domaine): Collection;
    public function findById(string $id): ?Filiere;
    public function search(string $query): Collection;
    public function create(array $data): Filiere;
    public function update(string $id, array $data): Filiere;
    public function delete(string $id): bool;
}

// TagRepositoryInterface.php
interface TagRepositoryInterface
{
    public function all(): Collection;
    public function byCategory(string $category): Collection;
    public function findBySlug(string $slug): ?Tag;
    public function create(array $data): Tag;
    public function update(string $id, array $data): Tag;
    public function delete(string $id): bool;
}
```

### 3.2 — Implémentations (Eloquent)

Placer dans `app/Repositories/Eloquent/`

**Exemple complet : SerieRepository**

```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Serie;
use App\Repositories\Contracts\SerieRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SerieRepository implements SerieRepositoryInterface
{
    private const CACHE_TTL = 3600; // 1 heure

    public function all(): Collection
    {
        return Cache::remember('series.all', self::CACHE_TTL, fn() =>
            Serie::orderBy('ordre')->get()
        );
    }

    public function findById(string $id): ?Serie
    {
        return Cache::remember("serie.{$id}", self::CACHE_TTL, fn() =>
            Serie::find($id)
        );
    }

    public function findByCode(string $code): ?Serie
    {
        return Cache::remember("serie.code.{$code}", self::CACHE_TTL, fn() =>
            Serie::where('code', $code)->first()
        );
    }

    public function actif(): Collection
    {
        return Cache::remember('series.actif', self::CACHE_TTL, fn() =>
            Serie::actif()->orderBy('ordre')->get()
        );
    }

    public function create(array $data): Serie
    {
        $serie = Serie::create($data);
        $this->invalidateCache();
        return $serie;
    }

    public function update(string $id, array $data): Serie
    {
        $serie = Serie::findOrFail($id);
        $serie->update($data);
        $this->invalidateCache();
        return $serie;
    }

    public function delete(string $id): bool
    {
        $result = Serie::destroy($id);
        $this->invalidateCache();
        return $result > 0;
    }

    private function invalidateCache(): void
    {
        Cache::forget('series.all');
        Cache::forget('series.actif');
        // Invalide aussi le cache spécifique de chaque serie
        Serie::all()->each(fn($s) => Cache::forget("serie.{$s->id}"));
    }
}
```

**Exemple : EtablissementRepository** (avec recherche + pagination)

```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Etablissement;
use App\Repositories\Contracts\EtablissementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EtablissementRepository implements EtablissementRepositoryInterface
{
    private const CACHE_TTL = 7200; // 2 heures

    public function all(): Collection
    {
        return Cache::remember('etablissements.all', self::CACHE_TTL, fn() =>
            Etablissement::verified()->get()
        );
    }

    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        return Etablissement::verified()
            ->orderBy('nom')
            ->paginate($perPage);
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

    public function ofType(string $type): Collection
    {
        return Cache::remember("etablissements.type.{$type}", self::CACHE_TTL, fn() =>
            Etablissement::verified()->ofType($type)->get()
        );
    }

    public function selectif(): Collection
    {
        return Cache::remember('etablissements.selectif', self::CACHE_TTL, fn() =>
            Etablissement::verified()->selectif()->get()
        );
    }

    public function search(string $query): Collection
    {
        // Pas d'antémémoire pour la recherche (variable)
        return Etablissement::verified()
            ->where('nom', 'ilike', "%{$query}%")
            ->orWhere('ville', 'ilike', "%{$query}%")
            ->orWhere('description', 'ilike', "%{$query}%")
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

    public function verify(string $id, User $verifier): bool
    {
        $etablissement = Etablissement::findOrFail($id);
        $etablissement->update([
            'is_verified'            => true,
            'verification_status'    => 'approved',
            'verified_by_user_id'    => $verifier->id,
            'verified_at'            => now(),
        ]);
        Cache::forget("etablissement.{$id}");
        $this->invalidateCache();
        return true;
    }

    private function invalidateCache(): void
    {
        Cache::forget('etablissements.all');
        Cache::forget('etablissements.verified');
        Cache::forget('etablissements.selectif');
        // Invalide aussi les filtres
        collect(['maritime', 'plateaux', 'centrale', 'kara', 'savanes'])
            ->each(fn($r) => Cache::forget("etablissements.region.{$r}"));
        collect(['lycee', 'universite', 'ecole_privee', 'centre_formation'])
            ->each(fn($t) => Cache::forget("etablissements.type.{$t}"));
    }
}
```

---

## 4. SERVICES MÉTIER

Services orchestrent la logique complexe, délèguent aux repositories, lancent des events.

Placer dans `app/Services/Domain/`

### 4.1 — SerieService

```php
<?php

namespace App\Services\Domain;

use App\Models\Serie;
use App\Repositories\Contracts\SerieRepositoryInterface;
use Illuminate\Support\Collection;

class SerieService
{
    public function __construct(
        private SerieRepositoryInterface $serieRepository,
    ) {}

    /**
     * Obtient tous les séries avec leurs matières et coefficients
     */
    public function getTousLesSeries(): Collection
    {
        return $this->serieRepository->actif()
            ->load('matieres');
    }

    /**
     * Trouve les séries accessibles selon une moyenne donnée
     *
     * @return Collection Séries triées par probabilité d'accès (décroissant)
     */
    public function findAccessibleSeries(float $moyenne): Collection
    {
        return $this->serieRepository->actif()
            ->filter(fn(Serie $s) => $moyenne >= $s->moyenne_minimale)
            ->sortBy(fn(Serie $s) => $s->ordre)
            ->values();
    }

    /**
     * Calcule l'écart entre la moyenne et le seuil de chaque série
     */
    public function calculateSeriesEcart(float $moyenne): array
    {
        return $this->serieRepository->actif()
            ->map(fn(Serie $s) => [
                'serie_id'       => $s->id,
                'code'           => $s->code,
                'label'          => $s->label,
                'moyenne_requise' => $s->moyenne_minimale,
                'ecart'          => round($moyenne - $s->moyenne_minimale, 2),
                'accessible'     => $moyenne >= $s->moyenne_minimale,
            ])
            ->toArray();
    }
}
```

### 4.2 — EtablissementService

```php
<?php

namespace App\Services\Domain;

use App\Models\Etablissement;
use App\Repositories\Contracts\EtablissementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EtablissementService
{
    public function __construct(
        private EtablissementRepositoryInterface $repository,
    ) {}

    /**
     * Trouve les établissements accessibles selon la région et le type
     */
    public function findByRegionAndType(string $region, string $type = null): Collection
    {
        $query = $this->repository->inRegion($region);

        if ($type) {
            $query = $this->repository->ofType($type);
        }

        return $query;
    }

    /**
     * Recherche textuelle sur nom, ville, description
     */
    public function search(string $query): Collection
    {
        return $this->repository->search($query);
    }

    /**
     * Obtient les 50 établissements "sélectionnés" pour la v1
     */
    public function getSelectifs(): Collection
    {
        return $this->repository->selectif();
    }

    /**
     * Ajoute un établissement (admin)
     */
    public function create(array $data): Etablissement
    {
        return $this->repository->create($data);
    }

    /**
     * Met à jour un établissement (admin)
     */
    public function update(string $id, array $data): Etablissement
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Valide un établissement (admin)
     */
    public function verify(string $id, \App\Models\User $verifier): Etablissement
    {
        $this->repository->verify($id, $verifier);
        return $this->repository->findById($id);
    }
}
```

### 4.3 — MetierService

```php
<?php

namespace App\Services\Domain;

use App\Repositories\Contracts\MetierRepositoryInterface;
use Illuminate\Support\Collection;

class MetierService
{
    public function __construct(
        private MetierRepositoryInterface $repository,
    ) {}

    /**
     * Obtient les métiers "porteurs" (d'avenir)
     */
    public function getPorteurs(): Collection
    {
        return $this->repository->porteur();
    }

    /**
     * Obtient les métiers avec demande croissante
     */
    public function getEnDemande(): Collection
    {
        return $this->repository->enDemande();
    }

    /**
     * Recherche un métier par nom ou compétence
     */
    public function search(string $query): Collection
    {
        return $this->repository->search($query);
    }

    /**
     * Calcule le score d'intérêt pour un métier (pour plus tard avec quiz)
     */
    public function calculateMatchScore($metier, $competences): float
    {
        $requises = $metier->competences_requises ?? [];
        $matching = count(array_intersect($requises, $competences));
        return min(100, ($matching / count($requises)) * 100);
    }
}
```

### 4.4 — FiliereService

```php
<?php

namespace App\Services\Domain;

use App\Repositories\Contracts\FiliereRepositoryInterface;
use Illuminate\Support\Collection;

class FiliereService
{
    public function __construct(
        private FiliereRepositoryInterface $repository,
    ) {}

    /**
     * Obtient les principales filières (sélectionnées pour v1)
     */
    public function getPrincipales(): Collection
    {
        return $this->repository->selectif();
    }

    /**
     * Obtient les filières d'un domaine (sante, tech, droit, etc.)
     */
    public function getByDomaine(string $domaine): Collection
    {
        return $this->repository->byDomaine($domaine);
    }

    /**
     * Recherche une filière par nom
     */
    public function search(string $query): Collection
    {
        return $this->repository->search($query);
    }

    /**
     * Charge les données complètes d'une filière
     */
    public function getDetailed(string $id): array
    {
        $filiere = $this->repository->findById($id);

        return [
            'filiere'      => $filiere,
            'series'       => $filiere->series,
            'formations'   => $filiere->formations,
            'metiers'      => $filiere->metiers,
            'etablissements' => $filiere->etablissements,
        ];
    }
}
```

---

## 5. ACTIONS ATOMIQUES

Placer dans `app/Actions/Domain/`

Actions sont des single-responsibility units pour des opérations réutilisables.

```php
// CalculateSerieAccessibilityScore.php
<?php

namespace App\Actions\Domain;

use App\Models\Serie;

class CalculateSerieAccessibilityScore
{
    /**
     * Calcule un score d'accessibilité (0-100%) pour une série selon la moyenne
     */
    public function execute(float $moyenne, Serie $serie): int
    {
        $seuil = $serie->moyenne_minimale;
        
        if ($moyenne < $seuil) {
            // Scoring linéaire en dessous du seuil
            $ecart = $seuil - $moyenne;
            return max(0, 100 - ($ecart * 10)); // -10% par point manquant
        }

        if ($moyenne >= $seuil && $moyenne < $seuil + 2) {
            return 70; // Zone limite
        }

        return 100; // Au-dessus de seuil + 2 points = bien accessible
    }
}

// PublishEtablissement.php
<?php

namespace App\Actions\Domain;

use App\Models\Etablissement;
use App\Models\User;

class PublishEtablissement
{
    /**
     * Publie un établissement (le vérifie + le marque sélectionné)
     */
    public function execute(Etablissement $etablissement, User $verifier): Etablissement
    {
        $etablissement->update([
            'is_verified'         => true,
            'verification_status' => 'approved',
            'verified_by_user_id' => $verifier->id,
            'verified_at'         => now(),
            'est_selectionne'     => true,
        ]);

        return $etablissement->refresh();
    }
}

// BulkImportEtablissements.php
<?php

namespace App\Actions\Domain;

use App\Models\Etablissement;
use DB;

class BulkImportEtablissements
{
    /**
     * Importe en masse les établissements depuis un CSV
     * Utilisé pour seeder les 100+ établissements Togo
     */
    public function execute(array $etablissementsData, ?User $verifier = null): int
    {
        $count = 0;

        DB::transaction(function () use ($etablissementsData, $verifier, &$count) {
            foreach ($etablissementsData as $data) {
                Etablissement::updateOrCreate(
                    ['slug' => Str::slug($data['nom'])],
                    array_merge($data, [
                        'is_verified'     => !!$verifier,
                        'verified_by_user_id' => $verifier?->id,
                        'verified_at'     => $verifier ? now() : null,
                    ])
                );
                $count++;
            }
        });

        return $count;
    }
}
```

---

## 6. FORM REQUESTS

Placer dans `app/Http/Requests/Domain/`

Validation + autorisation (pour admin)

```php
// SerieRequest.php
<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;

class SerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'code'                => ['required', 'string', 'max:3', 'unique:series,code'],
            'label'               => ['required', 'string', 'max:100'],
            'description'         => ['required', 'string'],
            'moyenne_minimale'    => ['required', 'numeric', 'between:0,20'],
            'profil_requis'       => ['required', 'string'],
            'apres_bac'           => ['required', 'string'],
            'tips_amelioration'   => ['nullable', 'json'],
            'ordre'               => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}

// EtablissementRequest.php
<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;

class EtablissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('editor|admin');
    }

    public function rules(): array
    {
        return [
            'nom'                 => ['required', 'string', 'max:255'],
            'type'                => ['required', 'in:lycee,universite,ecole_privee,centre_formation'],
            'region'              => ['required', 'in:maritime,plateaux,centrale,kara,savanes'],
            'ville'               => ['required', 'string', 'max:100'],
            'adresse'             => ['required', 'string'],
            'contact_email'       => ['nullable', 'email'],
            'contact_phone'       => ['nullable', 'string', 'max:20'],
            'site_web'            => ['nullable', 'url'],
            'description'         => ['required', 'string'],
            'frais_indicatifs'    => ['required', 'string'],
            'specialites'         => ['nullable', 'array'],
            'specialites.*'       => ['string', 'max:100'],
            'coord_latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'coord_longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}

// MetierRequest.php
<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;

class MetierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('editor|admin');
    }

    public function rules(): array
    {
        return [
            'nom'                     => ['required', 'string', 'max:255', 'unique:metiers,nom'],
            'description'             => ['required', 'string', 'max:500'],
            'competences_requises'    => ['required', 'array', 'min:1'],
            'competences_requises.*'  => ['string', 'max:100'],
            'demande_marche'          => ['required', 'integer', 'between:1,10'],
            'salaire_min'             => ['required', 'integer', 'min:0'],
            'salaire_max'             => ['required', 'integer', 'gt:salaire_min'],
            'description_longue'      => ['nullable', 'string'],
            'est_porteur'             => ['boolean'],
        ];
    }
}
```

---

## 7. CONTROLLERS API

Placer dans `app/Http/Controllers/Api/V1/Domain/`

Controllers minces : validation via FormRequest, logique via Service, réponse via Resource.

```php
// SerieController.php
<?php

namespace App\Http\Controllers\Api\V1\Domain;

use App\Http\Controllers\Controller;
use App\Http\Resources\Domain\SerieResource;
use App\Services\Domain\SerieService;
use Illuminate\Http\JsonResponse;

class SerieController extends Controller
{
    public function __construct(
        private SerieService $service,
    ) {}

    /**
     * GET /api/v1/series
     */
    public function index(): JsonResponse
    {
        $series = $this->service->getTousLesSeries();
        return $this->success(SerieResource::collection($series));
    }

    /**
     * GET /api/v1/series/{id}
     */
    public function show(string $id): JsonResponse
    {
        $serie = $this->service->findById($id);

        if (!$serie) {
            return $this->error('Serie not found', status: 404);
        }

        return $this->success(new SerieResource($serie));
    }
}

// EtablissementController.php
<?php

namespace App\Http\Controllers\Api\V1\Domain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\EtablissementRequest;
use App\Http\Resources\Domain\EtablissementResource;
use App\Services\Domain\EtablissementService;
use Illuminate\Http\JsonResponse;

class EtablissementController extends Controller
{
    public function __construct(
        private EtablissementService $service,
    ) {}

    /**
     * GET /api/v1/etablissements?region=maritime&type=lycee&search=tokoin
     */
    public function index(): JsonResponse
    {
        $region = request('region');
        $type   = request('type');
        $search = request('search');

        if ($search) {
            $etablissements = $this->service->search($search);
        } else {
            $etablissements = $this->service->findByRegionAndType($region, $type);
        }

        return $this->success(EtablissementResource::collection($etablissements));
    }

    /**
     * GET /api/v1/etablissements/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $etablissement = $this->service->findBySlug($slug);

        if (!$etablissement) {
            return $this->error('Etablissement not found', status: 404);
        }

        return $this->success(new EtablissementResource($etablissement->load('formations', 'series')));
    }

    /**
     * POST /api/v1/etablissements (admin)
     */
    public function store(EtablissementRequest $request): JsonResponse
    {
        $etablissement = $this->service->create($request->validated());
        return $this->success(new EtablissementResource($etablissement), status: 201);
    }

    /**
     * PATCH /api/v1/etablissements/{id} (admin)
     */
    public function update(string $id, EtablissementRequest $request): JsonResponse
    {
        $etablissement = $this->service->update($id, $request->validated());
        return $this->success(new EtablissementResource($etablissement));
    }

    /**
     * POST /api/v1/etablissements/{id}/verify (admin)
     */
    public function verify(string $id): JsonResponse
    {
        $etablissement = $this->service->verify($id, auth()->user());
        return $this->success(new EtablissementResource($etablissement));
    }
}

// MetierController.php
<?php

namespace App\Http\Controllers\Api\V1\Domain;

use App\Http\Controllers\Controller;
use App\Http\Resources\Domain\MetierResource;
use App\Services\Domain\MetierService;
use Illuminate\Http\JsonResponse;

class MetierController extends Controller
{
    public function __construct(
        private MetierService $service,
    ) {}

    /**
     * GET /api/v1/metiers?search=informatique&porteur=true
     */
    public function index(): JsonResponse
    {
        $search = request('search');
        $porteur = request('porteur') === 'true';

        if ($search) {
            $metiers = $this->service->search($search);
        } elseif ($porteur) {
            $metiers = $this->service->getPorteurs();
        } else {
            $metiers = $this->service->getEnDemande();
        }

        return $this->success(MetierResource::collection($metiers));
    }

    /**
     * GET /api/v1/metiers/{id}
     */
    public function show(string $id): JsonResponse
    {
        $metier = $this->service->findById($id);

        if (!$metier) {
            return $this->error('Métier not found', status: 404);
        }

        return $this->success(new MetierResource($metier->load('formations', 'filieres')));
    }
}

// FiliereController.php
<?php

namespace App\Http\Controllers\Api\V1\Domain;

use App\Http\Controllers\Controller;
use App\Http\Resources\Domain\FiliereResource;
use App\Services\Domain\FiliereService;
use Illuminate\Http\JsonResponse;

class FiliereController extends Controller
{
    public function __construct(
        private FiliereService $service,
    ) {}

    /**
     * GET /api/v1/filieres?domaine=sante
     */
    public function index(): JsonResponse
    {
        $domaine = request('domaine');
        $search = request('search');

        if ($search) {
            $filieres = $this->service->search($search);
        } elseif ($domaine) {
            $filieres = $this->service->getByDomaine($domaine);
        } else {
            $filieres = $this->service->getPrincipales();
        }

        return $this->success(FiliereResource::collection($filieres));
    }

    /**
     * GET /api/v1/filieres/{id}
     * Retourne la filière complète avec ses séries, formations, métiers
     */
    public function show(string $id): JsonResponse
    {
        $detailed = $this->service->getDetailed($id);

        if (!$detailed['filiere']) {
            return $this->error('Filière not found', status: 404);
        }

        return $this->success([
            'filiere'       => new FiliereResource($detailed['filiere']),
            'series'        => [...],
            'formations'    => [...],
            'metiers'       => [...],
        ]);
    }
}
```

---

## 8. RESOURCES API

Placer dans `app/Http/Resources/Domain/`

Transformation des modèles en JSON.

```php
// SerieResource.php
<?php

namespace App\Http\Resources\Domain;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SerieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'code'                 => $this->code,
            'label'                => $this->label,
            'description'          => $this->description,
            'moyenne_minimale'     => $this->moyenne_minimale,
            'profil_requis'        => $this->profil_requis,
            'apres_bac'            => $this->apres_bac,
            'tips_amelioration'    => $this->tips_amelioration,
            'matieres'             => MatiereResource::collection($this->whenLoaded('matieres')),
            'filieres'             => FiliereResource::collection($this->whenLoaded('filieres')),
        ];
    }
}

// EtablissementResource.php
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
            'is_public'        => $this->is_public,
            'is_verified'      => $this->is_verified,
            'verified_at'      => $this->verified_at?->toIso8601String(),
            'formations'       => FormationResource::collection($this->whenLoaded('formations')),
            'series'           => SerieResource::collection($this->whenLoaded('series')),
        ];
    }
}

// MetierResource.php
<?php

namespace App\Http\Resources\Domain;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'nom'                      => $this->nom,
            'description'              => $this->description,
            'competences_requises'     => $this->competences_requises,
            'demande_marche'           => $this->demande_marche,
            'salaire'                  => [
                'min'  => $this->salaire_min,
                'max'  => $this->salaire_max,
                'moyen' => ($this->salaire_min + $this->salaire_max) / 2,
            ],
            'est_porteur'              => $this->est_porteur,
            'formations'               => FormationResource::collection($this->whenLoaded('formations')),
            'filieres'                 => FiliereResource::collection($this->whenLoaded('filieres')),
        ];
    }
}

// FiliereResource.php
<?php

namespace App\Http\Resources\Domain;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiliereResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'nom'                 => $this->nom,
            'description'         => $this->description,
            'duree_estimee_ans'   => $this->duree_estimee_ans,
            'domaine_principal'   => $this->domaine_principal,
            'series'              => SerieResource::collection($this->whenLoaded('series')),
            'formations'          => FormationResource::collection($this->whenLoaded('formations')),
            'metiers'             => MetierResource::collection($this->whenLoaded('metiers')),
            'etablissements'      => EtablissementResource::collection($this->whenLoaded('etablissements')),
        ];
    }
}
```

---

## 9. ROUTES API

Dans `routes/api.php` ou créer `routes/domain.php` et l'inclure.

```php
// routes/domain.php
<?php

use App\Http\Controllers\Api\V1\Domain\{
    SerieController,
    EtablissementController,
    FormationController,
    MetierController,
    SecteurPorteurController,
    FiliereController,
    TagController,
};

Route::prefix('v1')->group(function () {
    // PUBLIC routes (read-only)
    Route::get('/series', [SerieController::class, 'index']);
    Route::get('/series/{id}', [SerieController::class, 'show']);

    Route::get('/etablissements', [EtablissementController::class, 'index']);
    Route::get('/etablissements/{slug}', [EtablissementController::class, 'show']);

    Route::get('/metiers', [MetierController::class, 'index']);
    Route::get('/metiers/{id}', [MetierController::class, 'show']);

    Route::get('/secteurs', [SecteurPorteurController::class, 'index']);
    Route::get('/secteurs/{id}', [SecteurPorteurController::class, 'show']);

    Route::get('/filieres', [FiliereController::class, 'index']);
    Route::get('/filieres/{id}', [FiliereController::class, 'show']);

    Route::get('/tags', [TagController::class, 'index']);

    // ADMIN routes (protected)
    Route::middleware(['auth:sanctum', 'role:editor|admin'])->group(function () {
        Route::post('/series', [SerieController::class, 'store']);
        Route::patch('/series/{id}', [SerieController::class, 'update']);
        Route::delete('/series/{id}', [SerieController::class, 'destroy']);

        Route::post('/etablissements', [EtablissementController::class, 'store']);
        Route::patch('/etablissements/{id}', [EtablissementController::class, 'update']);
        Route::delete('/etablissements/{id}', [EtablissementController::class, 'destroy']);
        Route::post('/etablissements/{id}/verify', [EtablissementController::class, 'verify']);

        Route::post('/metiers', [MetierController::class, 'store']);
        Route::patch('/metiers/{id}', [MetierController::class, 'update']);
        Route::delete('/metiers/{id}', [MetierController::class, 'destroy']);

        Route::post('/filieres', [FiliereController::class, 'store']);
        Route::patch('/filieres/{id}', [FiliereController::class, 'update']);
        Route::delete('/filieres/{id}', [FiliereController::class, 'destroy']);
    });
});
```

---

## 10. SEEDING DES DONNÉES

**Importance critique :** Le Domaine B ne vaut rien sans données réelles du Togo.

### 10.1 — DatabaseSeeder principal

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ordre CRITIQUE : respecter les dépendances
        $this->call([
            RegionSeeder::class,           // Enums de base
            TagSeeder::class,              // Taxonomie
            SerieSeeder::class,            // Les 7 séries + coefficients
            FiliereSeeder::class,          // Les 30-50 filières principales
            SecteurPorteurSeeder::class,   // Tech, Santé, Droit, etc.
            EtablissementSeeder::class,    // 100+ établissements Togo
            FormationSeeder::class,        // Diplômes offerts
            MetierSeeder::class,           // Fiches métier
            DonneeSectorielleSeeder::class, // Stats salariales
            RelationsSeeder::class,        // Pivot tables
        ]);
    }
}
```

### 10.2 — Exemple : SerieSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Serie;
use App\Models\MatiereCoefficient;
use Illuminate\Database\Seeder;

class SerieSeeder extends Seeder
{
    public function run(): void
    {
        $series = [
            [
                'code' => 'A',
                'label' => 'Lettres et Sciences Humaines',
                'description' => 'Pour les passionnés de langues, littérature, histoire...',
                'moyenne_minimale' => 10.0,
                'profil_requis' => 'Fort en français, anglais, histoire-géographie',
                'apres_bac' => 'Droit, Lettres, Journalisme, SHS, Tourisme',
                'ordre' => 1,
            ],
            [
                'code' => 'C',
                'label' => 'Mathématiques et Sciences Physiques',
                'description' => 'Pour les futurs ingénieurs, médecins, informaticiens...',
                'moyenne_minimale' => 12.0,
                'profil_requis' => 'Excellence en mathématiques et physique-chimie',
                'apres_bac' => 'Médecine, Ingénierie, Informatique, Pharmacie',
                'ordre' => 2,
            ],
            [
                'code' => 'D',
                'label' => 'Sciences de la Vie et de la Terre',
                'description' => 'Pour les futurs biologistes, médecins, agronomes...',
                'moyenne_minimale' => 11.0,
                'profil_requis' => 'Fort en SVT, chimie, mathématiques acceptables',
                'apres_bac' => 'Médecine, Pharmacie, Vétérinaire, Agronomie',
                'ordre' => 3,
            ],
            // F1, F2, F3, F4, G1, G2, G3...
        ];

        foreach ($series as $data) {
            Serie::create($data);
        }

        // Ajouter les matières et coefficients pour chaque série
        $this->attachMatieres();
    }

    private function attachMatieres(): void
    {
        // Série A
        Serie::where('code', 'A')->first()?->matieres()->createMany([
            ['matiere_nom' => 'Français', 'coefficient' => 4, 'note_minimale' => null],
            ['matiere_nom' => 'Anglais', 'coefficient' => 3, 'note_minimale' => null],
            ['matiere_nom' => 'Histoire-Géographie', 'coefficient' => 3, 'note_minimale' => null],
            ['matiere_nom' => 'Philosophie', 'coefficient' => 2, 'note_minimale' => null],
        ]);

        // Série C
        Serie::where('code', 'C')->first()?->matieres()->createMany([
            ['matiere_nom' => 'Mathématiques', 'coefficient' => 7, 'note_minimale' => 8.0],
            ['matiere_nom' => 'Physique-Chimie', 'coefficient' => 5, 'note_minimale' => null],
            ['matiere_nom' => 'Sciences de la Vie', 'coefficient' => 2, 'note_minimale' => null],
        ]);

        // Série D
        Serie::where('code', 'D')->first()?->matieres()->createMany([
            ['matiere_nom' => 'Sciences de la Vie', 'coefficient' => 6, 'note_minimale' => 10.0],
            ['matiere_nom' => 'Mathématiques', 'coefficient' => 4, 'note_minimale' => null],
            ['matiere_nom' => 'Physique-Chimie', 'coefficient' => 3, 'note_minimale' => null],
        ]);
    }
}
```

### 10.3 — Exemple : EtablissementSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use Illuminate\Database\Seeder;

class EtablissementSeeder extends Seeder
{
    public function run(): void
    {
        $etablissements = [
            [
                'nom' => 'Lycée de Tokoin',
                'type' => 'lycee',
                'region' => 'maritime',
                'ville' => 'Lomé',
                'adresse' => 'Tokoin, Lomé',
                'contact_email' => 'contact@lycedetokoin.tg',
                'contact_phone' => '+228 22 23 45 67',
                'site_web' => 'https://lycedetokoin.tg',
                'description' => 'Historique lycée de Tokoin, très sélectif',
                'frais_indicatifs' => 'Gratuit (Public)',
                'specialites' => ['A', 'C', 'D'],
                'is_public' => true,
                'is_verified' => true,
                'verification_status' => 'approved',
                'est_selectionne' => true,
                'coord_latitude' => 6.1256,
                'coord_longitude' => 1.2320,
            ],
            [
                'nom' => 'Université de Lomé',
                'type' => 'universite',
                'region' => 'maritime',
                'ville' => 'Lomé',
                'adresse' => 'Campus Universitaire, Lomé',
                'contact_email' => 'rectorat@ul.tg',
                'contact_phone' => '+228 22 23 00 00',
                'site_web' => 'https://ul.tg',
                'description' => 'Première université du Togo',
                'frais_indicatifs' => '10.000 - 50.000 FCFA',
                'specialites' => ['Médecine', 'Droit', 'Ingénierie'],
                'is_public' => true,
                'is_verified' => true,
                'verification_status' => 'approved',
                'est_selectionne' => true,
                'coord_latitude' => 6.1234,
                'coord_longitude' => 1.2345,
            ],
            // ...100+ établissements
        ];

        Etablissement::insert($etablissements);
    }
}
```

---

## 11. TESTS (PEST)

Placer dans `tests/Feature/Domain/` et `tests/Unit/Domain/`

```php
// tests/Feature/Domain/SerieTest.php
<?php

namespace Tests\Feature\Domain;

use App\Models\Serie;
use Tests\TestCase;

class SerieTest extends TestCase
{
    public function test_get_all_series(): void
    {
        Serie::factory()->count(5)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/series');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'label', 'description', 'moyenne_minimale'],
                ],
            ]);
    }

    public function test_get_single_serie(): void
    {
        $serie = Serie::factory()->create();

        $response = $this->getJson("/api/v1/series/{$serie->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $serie->id)
            ->assertJsonPath('data.code', $serie->code);
    }

    public function test_create_serie_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/series', [
            'code' => 'X',
            'label' => 'Test',
            'description' => 'Test',
            'moyenne_minimale' => 10,
            'profil_requis' => 'Test',
            'apres_bac' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_serie_as_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/series', [
                'code' => 'X',
                'label' => 'Nouvelle Série',
                'description' => 'Description test',
                'moyenne_minimale' => 10.0,
                'profil_requis' => 'Profil test',
                'apres_bac' => 'Débouchés test',
                'ordre' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'X');
    }
}

// tests/Feature/Domain/EtablissementTest.php
<?php

namespace Tests\Feature\Domain;

use App\Models\Etablissement;
use Tests\TestCase;

class EtablissementTest extends TestCase
{
    public function test_search_etablissements(): void
    {
        Etablissement::factory()->create(['nom' => 'Lycée de Tokoin', 'is_verified' => true]);
        Etablissement::factory()->create(['nom' => 'Lycée de Bè', 'is_verified' => true]);

        $response = $this->getJson('/api/v1/etablissements?search=tokoin');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_by_region(): void
    {
        Etablissement::factory()->create(['region' => 'maritime', 'is_verified' => true]);
        Etablissement::factory()->create(['region' => 'plateaux', 'is_verified' => true]);

        $response = $this->getJson('/api/v1/etablissements?region=maritime');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_unverified_etablissements_not_shown(): void
    {
        Etablissement::factory()->create(['is_verified' => false]);

        $response = $this->getJson('/api/v1/etablissements');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_verify_etablissement(): void
    {
        $admin = User::factory()->admin()->create();
        $etablissement = Etablissement::factory()->create(['is_verified' => false]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/etablissements/{$etablissement->id}/verify");

        $response->assertStatus(200);
        $this->assertTrue($etablissement->fresh()->is_verified);
    }
}

// tests/Unit/Domain/SerieServiceTest.php
<?php

namespace Tests\Unit\Domain;

use App\Models\Serie;
use App\Services\Domain\SerieService;
use Tests\TestCase;

class SerieServiceTest extends TestCase
{
    private SerieService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SerieService::class);
    }

    public function test_find_accessible_series_above_threshold(): void
    {
        Serie::factory()->create(['code' => 'A', 'moyenne_minimale' => 10.0]);
        Serie::factory()->create(['code' => 'C', 'moyenne_minimale' => 12.0]);
        Serie::factory()->create(['code' => 'D', 'moyenne_minimale' => 11.0]);

        $accessible = $this->service->findAccessibleSeries(12.5);

        $this->assertCount(3, $accessible);
    }

    public function test_calculate_series_ecart(): void
    {
        Serie::factory()->create(['code' => 'C', 'moyenne_minimale' => 12.0]);

        $ecarts = $this->service->calculateSeriesEcart(14.0);

        $this->assertArrayHasKey('code', $ecarts[0]);
        $this->assertEquals(2.0, $ecarts[0]['ecart']);
        $this->assertTrue($ecarts[0]['accessible']);
    }
}
```

---

## 12. CHECKLIST FINALE

- [ ] Tous les modèles créés avec migrations
- [ ] Relations bidirectionnelles correctes
- [ ] Repositories interfaces + implémentations
- [ ] Cache strategy mise en place
- [ ] Services métier complets
- [ ] Actions atomiques isolées
- [ ] Form Requests validation + autorisation
- [ ] Controllers minces et délégants
- [ ] Resources API pour chaque modèle
- [ ] Routes API groupées v1
- [ ] Seeders peuplent les données Togo
- [ ] Tests Pest (Unit + Feature) > 80% coverage
- [ ] Documentation API (Laravel Swagger/OpenAPI)
- [ ] Pagination sur les listes longues
- [ ] Recherche fulltext PostgreSQL
- [ ] Cache tags pour invalidation ciblée
- [ ] Soft deletes sur les contenus supprimables
- [ ] Timestamps et UUIDs partout
- [ ] Enums PHP pour les énumérations
- [ ] Media uploads (logos établissements, images)

---

*Domaine B — Plan d'Implémentation v1.0*
*OrientTogo · Laravel 13 · PHP 8.3 · Pest · Repository Pattern*
