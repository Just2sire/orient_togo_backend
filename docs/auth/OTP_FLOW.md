# Flux d'authentification par OTP

Le flux OTP permet une connexion rapide et une inscription automatique sans mot de passe.

## 📥 1. Demande de code (`POST /otp/send`)

**Processus :**
1. Validation du numéro de téléphone (Regex Togo).
2. Normalisation au format `+228XXXXXXXX`.
3. Vérification du rate-limit (Max 5 codes par heure par numéro).
4. Invalidation de tous les anciens codes non utilisés pour ce numéro.
5. Génération d'un code aléatoire à 6 chiffres.
6. Hachage du code et stockage en base de données.
7. Envoi du code en clair via SMS (ou logs en local).

## 📤 2. Vérification et Connexion (`POST /otp/verify`)

**Processus :**
1. Recherche du dernier code valide (non expiré, non utilisé, < 3 tentatives).
2. Incrémentation immédiate du compteur de tentatives (`attempts`).
3. Vérification du code fourni contre le hash en base de données (`Hash::check`).
4. **Si invalide :** Retourne une erreur. Si 3 échecs atteints, marque l'OTP comme `is_used` (invalidé).
5. **Si valide :**
   - Marque l'OTP comme `is_used` et enregistre la date (`used_at`).
   - Recherche l'utilisateur par téléphone.
   - **Inscription Auto :** Si l'utilisateur n'existe pas, il est créé avec le rôle `Student` et un email par défaut (`phone@orienttogo.tg`).
   - Émission d'un token **Laravel Sanctum**.
   - Retourne le token, les infos utilisateur, et un flag `is_new_user`.

## 🛡️ Règles de Sécurité
- **Durée de vie :** 10 minutes.
- **Blocage :** Invalidation définitive du code après 3 erreurs de saisie.
- **Traçabilité :** L'adresse IP de la demande est enregistrée pour chaque code généré.
