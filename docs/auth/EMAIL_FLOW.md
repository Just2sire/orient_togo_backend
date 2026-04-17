# Flux d'authentification par Email

Le flux classique pour les utilisateurs préférant une gestion par mot de passe.

## 📝 1. Inscription (`POST /register`)

**Processus :**
1. Validation de l'email (unique en base) et du mot de passe (confirmation requise, min 8 caractères).
2. Création de l'utilisateur avec le rôle par défaut (`Student`).
3. Émission d'un token **Sanctum**.
4. Retourne le token et les données utilisateur.

## 🔑 2. Connexion (`POST /login`)

**Processus :**
1. Vérification des identifiants (email + mot de passe).
2. Vérification du statut du compte (`is_active`).
3. Émission d'un token **Sanctum**.
4. Retourne le token et les données utilisateur.

## 🛡️ Règles de Sécurité
- **Comptes Inactifs :** Un utilisateur dont le champ `is_active` est `false` ne peut pas se connecter (retourne une erreur spécifique).
- **Hachage :** Les mots de passe sont automatiquement hachés via le cast `hashed` du modèle `User`.
- **Nom du Device :** Les clients peuvent envoyer un `device_name` pour identifier leurs sessions dans le dashboard utilisateur.
