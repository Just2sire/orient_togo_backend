# Tests de l'Authentification

Le module Auth est couvert par une suite de tests fonctionnels (Feature Tests) utilisant **PHPUnit**.

## 🏃 Exécution des tests

Pour lancer uniquement les tests liés à l'authentification :

```bash
php artisan test --compact tests/Feature/Auth
```

## 📊 Couverture

### 1. `OtpAuthTest`
- [x] Envoi d'OTP avec succès.
- [x] Inscription automatique via OTP valide.
- [x] Connexion d'un utilisateur existant via OTP.
- [x] Échec sur code expiré.
- [x] Échec sur code incorrect (avec incrémentation des tentatives).
- [x] Invalidation automatique après 3 échecs.
- [x] Rate limiting (Max 5 demandes par heure).

### 2. `EmailAuthTest`
- [x] Inscription classique par email.
- [x] Empêcher les doublons d'email.
- [x] Connexion par email avec succès.
- [x] Échec sur mauvais identifiants.
- [x] Blocage des comptes inactifs.

### 3. `SessionTest`
- [x] Récupération du profil (`/me`).
- [x] Déconnexion simple (révocation du token courant).
- [x] Déconnexion de tous les appareils.

## 🛠️ Configuration des tests
Les tests utilisent le trait `RefreshDatabase` et lancent automatiquement le `RolePermissionSeeder` pour garantir que les rôles nécessaires sont présents.
Le service SMS est "mocké" pour ne pas effectuer d'appels réels durant les tests.
