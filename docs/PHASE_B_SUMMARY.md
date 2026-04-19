# Phase B : Contenu Éducatif — Résumé d'Implémentation

Ce document répertorie tous les composants ajoutés ou modifiés lors de l'implémentation du Domaine B (Séries, Établissements, Formations, Métiers, Secteurs).

## 🛠️ Nouveaux Modèles & Relations

Tous les nouveaux modèles utilisent **UUID v7** (via Laravel 11+) et l'attribut PHP 8 `#[Fillable]`.

### 1. `Serie`
- **Description** : Représente les séries du lycée (A, C, D, etc.).
- **Relations** :
  - `subjectCoefficients` (HasMany)
  - `fields` (BelongsToMany)
  - `establishments` (BelongsToMany)
- **Casts** : `tips` (array), `is_active` (boolean).

### 2. `SubjectCoefficient`
- **Description** : Coefficients des matières par série.
- **Relations** : `serie` (BelongsTo).

### 3. `Establishment`
- **Description** : Lycées, Universités, Instituts.
- **Relations** :
  - `courses` (HasMany)
  - `fields` (BelongsToMany)
  - `series` (BelongsToMany)
  - `verifiedBy` (BelongsTo User)
  - `tags` (MorphToMany)
- **Casts** : `type` (EstablishmentTypeEnum), `region` (RegionEnum), `is_verified` (boolean), `is_selected` (boolean).

### 4. `Course`
- **Description** : Formations/Diplômes spécifiques (ex: Licence Génie Logiciel).
- **Relations** :
  - `establishment` (BelongsTo)
  - `careers` (BelongsToMany)
- **Casts** : `level` (LevelEnum).

### 5. `Career`
- **Description** : Fiches métiers.
- **Relations** :
  - `growthSector` (BelongsTo)
  - `sectorData` (HasMany)
  - `fields` (BelongsToMany)
  - `courses` (BelongsToMany)
- **Casts** : `required_skills` (array), `is_promising` (boolean).

### 6. `GrowthSector`
- **Description** : Secteurs économiques porteurs.
- **Relations** : `careers` (HasMany), `fields` (BelongsToMany).

### 7. `Field`
- **Description** : Filières d'études (ex: Santé, Ingénierie).
- **Relations** : `series`, `establishments`, `careers`, `growthSectors` (tous BelongsToMany).

### 8. `Tag`
- **Description** : Système de tag polymorphe pour filtrage.
- **Relations** : `establishments`, `careers`, `fields` (MorphedByMany).

---

## 🏗️ Pattern Repository & Service

Chaque entité suit le pattern : **Controller → Service → Repository → Model**.

### Services Ajoutés (`app/Services/`)
- `SerieService` : Calcul d'accessibilité selon la moyenne.
- `EstablishmentService` : Validation admin et recherche géographique.
- `FieldService` : Agrégation des données filières.
- `CareerService`, `GrowthSectorService`, `CourseService` : Gestion CRUD et relations.

### Repositories Ajoutés (`app/Repositories/`)
- Interfaces dans `app/Repositories/Contracts/`.
- Implémentations dans `app/Repositories/`.
- **Bindings** configurés dans `RepositoryServiceProvider`.

---

## 🚦 API & Documentation

### Nouveaux Endpoints (`/api/v1/`)
- **Lecture Publique** : `GET /series`, `GET /establishments`, `GET /fields`, etc.
- **Ecriture (Auth)** : `POST /series`, `PATCH /establishments/{id}/verify`, etc.
- **Spécial** : `GET /series/accessible?average=12.5`.

### Swagger
Documentation auto-générée accessible via `/api/documentation`. Correction des stubs de génération pour supporter les nouveaux attributs PHP 8.

---

## 🧪 Tests & Données
- **Seeders** : `SerieSeeder`, `EstablishmentSeeder`, `FieldSeeder`, `GrowthSectorSeeder`, `CareerSeeder`, `CourseSeeder`, `DomainRelationsSeeder`.
- **Factories** : Toutes les factories Phase B sont prêtes et produisent des données valides pour Postgres.
- **Tests PHPUnit** : `SerieTest` et `EstablishmentTest` valident les flux principaux.

---
*Dernière mise à jour : 18 Avril 2026*
