# Repository Pattern — Implémentation Auth

L'application utilise le Repository Pattern pour découpler la logique métier de la persistance des données.

## 👤 UserRepository

Interface : `App\Repositories\Contracts\UserRepositoryInterface`

**Méthodes spécifiques à l'Auth :**
- `findByEmail(string $email)` : Trouve un utilisateur via son email (ignore les scopes globaux).
- `findByPhone(string $phone)` : Trouve un utilisateur via son numéro normalisé.
- `createFromOtp(string $phone)` : Inscription rapide. Crée l'user, assigne le rôle `Student` et génère un email fictif.
- `createWithPassword(string $email, string $password)` : Inscription classique.

## 🔑 OtpCodeRepository

Interface : `App\Repositories\Contracts\OtpCodeRepositoryInterface`

**Méthodes spécifiques à l'Auth :**
- `createForPhone(...)` : Enregistre un nouvel OTP haché avec sa date d'expiration.
- `findValidForPhone(string $phone, string $type)` : Récupère le dernier OTP non expiré, non utilisé et non épuisé.
- `countRecentForPhone(string $phone, int $minutes)` : Utilisé pour le rate-limiting.
- `invalidatePreviousForPhone(string $phone, string $type)` : Marque les anciens codes comme utilisés pour forcer l'usage du dernier reçu.

## 🔌 Binding

Les repositories sont liés à leurs interfaces dans `App\Providers\RepositoryServiceProvider`.

```php
$this->app->bind(
    UserRepositoryInterface::class,
    UserRepository::class
);
```
