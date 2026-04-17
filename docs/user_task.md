📋 Plan d'action — Domaine B : Profils & Préférences

  Objectifs
   - Permettre aux utilisateurs de compléter leur profil (onboarding).
   - Gérer les préférences (notifications, dark mode, niveau scolaire).
   - Enregistrer les appareils pour les futures notifications push.
   - Gérer les favoris (Morphed relation).

  ---

  ÉTAPE 1 — Préparation des Modèles & Factories (10 min)
  Les modèles sont déjà bien avancés. Il s'agit juste de s'assurer que les factories sont complètes pour les tests.
   - [ ] Vérifier/Compléter UserProfileFactory.
   - [ ] Vérifier/Compléter UserDeviceFactory.
   - [ ] Créer UserFavoriteFactory (si non existante).

  ÉTAPE 2 — Génération du Pattern via RepoPattern (10 min)
  Utilisation de ta commande pour gagner du temps sur la structure de base.

   1 php artisan app:repo-pattern UserProfile --repository --request --resource --swagger
   2 php artisan app:repo-pattern UserDevice --repository --request --resource --swagger
   3 php artisan app:repo-pattern UserFavorite --repository --request --resource --swagger

  ÉTAPE 3 — Implémentation de la Logique Métier (30 min)
  Le "cœur" du domaine B se trouve dans les services.

  👤 UserProfileService
   - update(User $user, array $data) : Mise à jour des informations (ville, région, classe).
   - completeOnboarding(User $user) : Marquer le profil comme terminé.
   - updatePreferences(User $user, array $prefs) : Dark mode, notifications, etc.

  📱 UserDeviceService
   - register(User $user, array $data) : Enregistre ou met à jour un push_token pour un appareil donné.
   - refreshActivity(User $user, string $pushToken) : Met à jour la date last_active_at.

  ❤️ UserFavoriteService
   - toggle(User $user, string $type, string $id) : Ajoute ou retire un élément des favoris (Quiz, Cours, etc.).

  ÉTAPE 4 — Controllers & Routes (20 min)
  Organisation des routes sous /api/v1/.

    1 // Routes protégées par auth:sanctum
    2 Route::prefix('user')->group(function () {
    3     Route::get('profile', [UserProfileController::class, 'show']);
    4     Route::put('profile', [UserProfileController::class, 'update']);
    5     Route::patch('preferences', [UserProfileController::class, 'updatePreferences']);
    6     
    7     Route::post('devices', [UserDeviceController::class, 'store']);
    8     Route::get('favorites', [UserFavoriteController::class, 'index']);
    9     Route::post('favorites/toggle', [UserFavoriteController::class, 'toggle']);
   10 });

  ÉTAPE 5 — Documentation Swagger & Tests (20 min)
   - [ ] Annoter les nouveaux endpoints avec les Attributes PHP 8.
   - [ ] Créer les tests de feature :
       - ProfileTest.php : Update, Preferences.
       - DeviceTest.php : Registration.
       - FavoriteTest.php : Toggle, Listing.

  ---

  🛡️ Points d'attention
   1. Unique Username : S'assurer que le username est unique lors de l'update.
   2. Relations Morphed : Pour les favoris, bien valider que le favorable_type appartient à une liste autorisée.
   3. Audit Logs : Utiliser ton trait HasAuditLog pour tracer les changements de profil critiques.  