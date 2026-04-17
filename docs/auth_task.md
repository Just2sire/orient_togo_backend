# Auth — État des lieux & Plan d'action
## OrientTogo · Laravel 13 · PHP 8.3

---

## Ce qui est déjà bien fait

Avant de parler de ce qui manque ou pose problème, voilà ce qui est solide et à garder tel quel.

**Les Models** sont propres. L'utilisation de `#[Fillable]` en attribut PHP 8 est moderne et lisible. `HasUuids` natif Laravel est le bon choix (pas besoin d'un trait custom). `OtpCode` avec `$timestamps = false` et `isValid()` / `markAsUsed()` est exactement ce qu'il faut. `UserProfile`, `UserDevice`, `UserFavorite` sont bien structurés avec leurs casts et relations.

**La commande `RepoPattern`** est une excellente idée — elle va te faire gagner beaucoup de temps sur les domaines suivants (B, C, D...). Elle est bien codée avec dry-run, rollback, et le trait `ApiResponse` intégré est complet.

**Les Enums** couvrent tout le besoin. `OtpTypeEnum`, `PlatformEnum`, `RegionEnum`, `SchoolLevelEnum`, `UserRoleEnum` — c'est propre.

**`HasAuditLog`** et `AuditLogService` montrent une bonne intention de traçabilité dès le début.

---

## Ce qui pose problème

### 1. Le `OtpCode` manque de champs critiques

Le model actuel n'a pas de colonne `phone`, ni de compteur de tentatives `attempts`. Sans `phone`, impossible de retrouver l'OTP d'un utilisateur. Sans `attempts`, impossible de bloquer le brute-force (quelqu'un qui essaie 1000 codes).

```
Manquant dans OtpCode :
  - phone      → pour qui est cet OTP ?
  - attempts   → combien de fois a-t-on tenté ?
  - ip_address → traçabilité
```

### 2. Le `User` a des relations commentées

Les relations `userProfile()`, `userFavorites()`, `userDevices()` sont en commentaire. C'est le bon moment de les décommenter — ces models existent maintenant.

### 3. `AuthService` existe mais son contenu n'est pas visible

Le fichier est dans `Http/Services/AuthService.php`. La bonne place selon l'architecture établie est `App/Services/Auth/AuthService.php`. C'est un détail mais ça compte pour la cohérence quand le projet grossit.

### 4. Il n'y a pas encore de Repository pour l'auth

La commande `RepoPattern` génère des repositories, mais rien n'a encore été généré pour `User` et `OtpCode`. Sans ça, le service fait du SQL direct via les Models — ce qui rend les tests difficiles et couplage fort.

### 5. La `todolist` originale est trop large pour être actionnable maintenant

Elle couvre 12 phases dont beaucoup chevauchent des choses déjà faites (Phases 1, 3, 4 = done). Elle inclut aussi des choses qui ne concernent pas l'auth pure (favoris, devices, admin). L'ordre n'est pas optimal par rapport à où tu en es.

### 6. Pas de gestion OTP vs Email — le flux n'est pas tranché

L'ancienne todolist liste `register` et `login` génériques sans distinguer les deux canaux (OTP phone vs email+password). Il faut décider dès maintenant quelle route fait quoi, sinon l'`AuthService` va grossir sans structure.

---

## Décisions à prendre maintenant (avant de coder)

### Canal d'auth pour la v1

```
✅ Phone + OTP     → connexion ET inscription automatique (pas de mdp)
✅ Email + password → connexion ET inscription classique
⏳ OAuth Google    → phase 2 (pas maintenant)
```

### OTP — durée de vie et sécurité

```
✅ TTL          : 10 minutes
✅ Tentatives   : max 3 par OTP, 5 OTP par heure par numéro
✅ Stockage code: hashé (Hash::make) jamais en clair en DB
✅ Après 3 échecs : OTP invalidé, l'utilisateur doit en demander un nouveau
```

### Token Sanctum

```
✅ Durée        : 90 jours (expiration dans config/sanctum.php)
✅ Révocation   : token courant uniquement par défaut, tous si all_devices=true
✅ Nom du token : device_name transmis par le client (ex: "Chrome Android")
```

---

## Plan d'action révisé

Les phases 0 à 4 de l'ancienne liste sont **partiellement faites**. On repart de l'état actuel.

---

### ÉTAPE 1 — Corriger OtpCode (15 min)

Le model et la migration doivent être alignés. Vérifier que la migration `create_otp_codes_table` contient bien :

```php
// Ce qui doit être dans la migration (pas dans le model)
$table->string('phone', 20)->index();
$table->string('code');             // hashé en DB
$table->string('type', 50);        // 'login' | 'register' | 'verify_phone'
$table->tinyInteger('attempts')->default(0);
$table->boolean('is_used')->default(false);  // → remplace used_at comme flag principal
$table->timestamp('expires_at');
$table->timestamp('used_at')->nullable();
$table->string('ip_address', 45)->nullable();
$table->timestamp('created_at')->useCurrent();
// PAS de updated_at
```

Mettre à jour `OtpCode.php` pour ajouter `phone`, `attempts`, `is_used` dans le fillable et les casts. Ajouter la méthode `isExhausted()`.

```php
// Ajouts dans OtpCode.php

#[Fillable(['phone', 'code', 'type', 'attempts', 'is_used', 'expires_at', 'used_at', 'ip_address'])]

// Dans casts()
'is_used'    => 'boolean',
'attempts'   => 'integer',

// Méthodes à ajouter
public function isExhausted(): bool
{
    return $this->attempts >= 3;
}

public function isValid(): bool
{
    return !$this->is_used
        && !$this->isExhausted()
        && $this->expires_at->isFuture();
}

public function markAsUsed(): void
{
    $this->update(['is_used' => true, 'used_at' => now()]);
}
```

---

### ÉTAPE 2 — Décommenter les relations User (5 min)

Dans `User.php`, décommenter les trois relations et ajouter `phone` au fillable :

```php
#[Fillable(['email', 'phone', 'password', 'role', 'is_active'])]

// Décommenter :
public function userProfile(): HasOne
{
    return $this->hasOne(UserProfile::class);
}

public function userFavorites(): HasMany
{
    return $this->hasMany(UserFavorite::class);
}

public function userDevices(): HasMany
{
    return $this->hasMany(UserDevice::class);
}

// Ajouter :
public function otpCodes(): HasMany
{
    return $this->hasMany(OtpCode::class, 'phone', 'phone');
}
```

Vérifier que la migration `users` a bien une colonne `phone varchar(20) nullable unique`.

---

### ÉTAPE 3 — Générer les Repositories avec ta commande (10 min)

```bash
php artisan app:repo-pattern User --repository --request --resource
php artisan app:repo-pattern OtpCode --repository
```

Cela génère :
- `app/Repositories/Contracts/UserRepositoryInterface.php`
- `app/Repositories/UserRepository.php`
- `app/Repositories/Contracts/OtpCodeRepositoryInterface.php`
- `app/Repositories/OtpCodeRepository.php`
- `app/Providers/RepositoryServiceProvider.php` (avec les bindings)

Ensuite **compléter manuellement** les interfaces avec les méthodes nécessaires à l'auth :

```php
// UserRepositoryInterface — méthodes à ajouter si non générées
public function findByEmail(string $email): ?User;
public function findByPhone(string $phone): ?User;
public function createFromOtp(string $phone): User;
public function createWithPassword(string $email, string $password): User;

// OtpCodeRepositoryInterface
public function createForPhone(string $phone, string $hashedCode, string $type): OtpCode;
public function findValidForPhone(string $phone, string $type): ?OtpCode;
public function countRecentForPhone(string $phone, int $minutes = 60): int;
public function invalidatePreviousForPhone(string $phone, string $type): void;
```

---

### ÉTAPE 4 — Déplacer et restructurer AuthService (20 min)

Déplacer `Http/Services/AuthService.php` vers `Services/Auth/AuthService.php`.

Créer aussi `Services/Auth/OtpService.php` et `Services/Auth/SmsService.php` séparément — l'`AuthService` ne doit pas gérer l'envoi de SMS directement.

**Structure cible :**
```
app/Services/Auth/
    AuthService.php     → orchestre login/register, émet les tokens
    OtpService.php      → génère, envoie, vérifie les OTP
    SmsService.php      → envoie les SMS (Togocom/Moov/fallback)
```

**Ce que fait chaque service :**

`OtpService` :
- `send(string $phone, string $type)` — génère le code, le hashe, l'enregistre via OtpRepository, envoie le SMS
- `verify(string $phone, string $code, string $type): OtpCode` — vérifie le code, incrémente les tentatives, lève une exception si invalide

`AuthService` :
- `loginWithOtp(string $phone, string $code, ?string $deviceName): array` — appelle OtpService->verify(), trouve ou crée l'User, émet le token Sanctum
- `loginWithEmail(string $email, string $password, ?string $deviceName): array` — vérifie credentials, émet le token
- `registerWithEmail(string $email, string $password, ?string $deviceName): array` — crée l'User, émet le token
- `logout(User $user, bool $allDevices = false): void` — révoque le(s) token(s)
- `normalizePhone(string $phone): string` — normalise vers E.164 (+228XXXXXXXX)

---

### ÉTAPE 5 — Form Requests Auth (20 min)

Créer `app/Http/Requests/Auth/` avec :

```
SendOtpRequest.php
VerifyOtpRequest.php
LoginWithEmailRequest.php
RegisterWithEmailRequest.php
```

Règles de validation importantes :

```php
// SendOtpRequest
'phone' => ['required', 'string', 'regex:/^(\+?228)?[0-9]{8}$/']
'type'  => ['sometimes', 'in:login,register']

// VerifyOtpRequest
'phone'       => ['required', 'string', 'regex:/^(\+?228)?[0-9]{8}$/']
'code'        => ['required', 'digits:6']
'device_name' => ['sometimes', 'nullable', 'string', 'max:255']
'platform'    => ['sometimes', 'nullable', 'in:android,ios,web']

// LoginWithEmailRequest
'email'       => ['required', 'email:rfc']
'password'    => ['required', 'string']
'device_name' => ['sometimes', 'nullable', 'string', 'max:255']

// RegisterWithEmailRequest
'email'                 => ['required', 'email:rfc', 'unique:users,email']
'password'              => ['required', 'confirmed', 'min:8']
'device_name'           => ['sometimes', 'nullable', 'string', 'max:255']
```

---

### ÉTAPE 6 — Controllers (20 min)

**Ne pas utiliser** la commande `RepoPattern` pour les controllers Auth — leur structure est trop différente des CRUD standards générés. Les créer manuellement dans `app/Http/Controllers/Api/Auth/`.

```
OtpController.php        → send(), verify()
EmailAuthController.php  → login(), register()
SessionController.php    → me(), logout()
```

Chaque méthode : max 8 lignes. Le controller valide (via Form Request), construit les données, délègue au Service, retourne la Resource.

```php
// Exemple — OtpController::verify()
public function verify(VerifyOtpRequest $request): JsonResponse
{
    $result = $this->authService->loginWithOtp(
        phone:      $this->authService->normalizePhone($request->validated('phone')),
        code:       $request->validated('code'),
        deviceName: $request->validated('device_name'),
    );

    return $this->success(new AuthTokenResource($result));
}
```

---

### ÉTAPE 7 — API Resource (10 min)

Un seul Resource pour la réponse d'auth — utilisé par tous les canaux :

```php
// app/Http/Resources/Auth/AuthTokenResource.php
public function toArray(Request $request): array
{
    return [
        'access_token' => $this->resource['token'],
        'token_type'   => 'Bearer',
        'expires_in'   => 90 * 24 * 3600,
        'user' => [
            'id'             => $this->resource['user']->id,
            'email'          => $this->resource['user']->email,
            'phone'          => $this->maskPhone($this->resource['user']->phone),
            'role'           => $this->resource['user']->role->value,
            'email_verified' => $this->resource['user']->email_verified_at !== null,
            'onboarding_done'=> $this->resource['user']->userProfile?->onboarding_done ?? false,
        ],
        'is_new_user' => $this->resource['is_new'] ?? false,
    ];
}
```

---

### ÉTAPE 8 — Routes (10 min)

```php
// routes/api.php
Route::prefix('v1')->middleware(['api'])->group(function () {

    Route::prefix('auth')->middleware('throttle:60,1')->group(function () {
        // OTP
        Route::post('otp/send',   [OtpController::class, 'send']);
        Route::post('otp/verify', [OtpController::class, 'verify']);

        // Email
        Route::post('login',    [EmailAuthController::class, 'login']);
        Route::post('register', [EmailAuthController::class, 'register']);

        // Session protégée
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me',      [SessionController::class, 'me']);
            Route::post('logout', [SessionController::class, 'logout']);
        });
    });
});
```

---

### ÉTAPE 9 — Tests Pest (30 min)

Créer `tests/Feature/Auth/` avec trois fichiers :

**`OtpAuthTest.php`**
```
✅ send OTP → 200 + SMS loggé (SmsService mocké)
✅ verify OTP valide → 200 + token + is_new_user true
✅ verify OTP → user existant, is_new_user false
❌ OTP expiré → 422
❌ OTP déjà utilisé → 422
❌ Code incorrect → 422 (et attempts incrémenté)
❌ 3 tentatives → OTP invalidé → 422
❌ Rate limit send : 6ème demande dans l'heure → 429
```

**`EmailAuthTest.php`**
```
✅ register email → 201 + token
✅ login email → 200 + token
❌ register email déjà pris → 422
❌ login email inconnu → 401 (message générique, pas "email introuvable")
❌ login mauvais password → 401 (même message générique)
❌ compte inactif → 403
```

**`SessionTest.php`**
```
✅ GET /me avec token valide → 200 + user
✅ logout → 200 + token révoqué
✅ logout all_devices → tous les tokens révoqués
❌ GET /me sans token → 401
❌ POST /logout sans token → 401
```

---

## Checklist finale — Auth v1 terminée quand :

- [ ] Migration `otp_codes` a les colonnes `phone`, `attempts`, `is_used`, `ip_address`
- [ ] `OtpCode::isValid()` vérifie non expiré ET non épuisé ET non utilisé
- [ ] `User` a `phone` en fillable et les 3 relations décommentées
- [ ] `UserRepository` et `OtpCodeRepository` existent et sont bindés
- [ ] `OtpService::send()` hashe le code avant de le stocker
- [ ] `OtpService::verify()` incrémente `attempts` AVANT de vérifier le code
- [ ] `AuthService` dans `Services/Auth/` (pas dans `Http/Services/`)
- [ ] `SmsService` log en local, prêt pour brancher Togocom/Moov en prod
- [ ] 3 controllers dans `Controllers/Api/Auth/` — max 8 lignes par méthode
- [ ] `AuthTokenResource` masque le numéro de téléphone (+228****3456)
- [ ] Routes versionnées sous `/api/v1/auth/`
- [ ] Tous les tests Pest ci-dessus passent au vert
- [ ] `php artisan route:list --path=api/v1/auth` ne montre aucune route non protégée oubliée
- [ ] Aucun `dd()` ou `var_dump()` dans le code

---

## Ce qui attend le Domaine B (ne pas faire maintenant)

- Profil utilisateur (lecture/écriture `UserProfile`)
- Gestion des favoris (`UserFavorite`)
- Enregistrement des devices (`UserDevice`)
- OAuth Google / Facebook
- Vérification email (lien par mail)
- Reset password
- Routes admin (gestion des users, audit logs)

Ces éléments dépendent d'un auth qui fonctionne. Une fois l'auth verte aux tests, on les traite dans l'ordre.

---

*OrientTogo — Auth v1 Plan · Laravel 13 · PHP 8.3 · Pest · Sanctum*