# Authentification — Vue d'ensemble

L'authentification dans OrientTogo est divisée en deux canaux principaux, gérés par **Laravel Sanctum** pour l'émission de jetons (tokens) API.

## 🚀 Architecture Technique

- **Framework :** Laravel 13
- **Langage :** PHP 8.3+
- **Sécurité :** Laravel Sanctum (Tokens via UUIDs)
- **Pattern :** Repository Pattern + Service Layer
- **Validation :** Form Requests spécifiques

## 🛠️ Composants Clés

### 1. Services (`app/Services/Auth`)
- **`AuthService`** : Orchestrateur principal. Gère les connexions, inscriptions et la normalisation des données.
- **`OtpCodeService`** : Logique métier liée aux OTP (génération, hachage, vérification, rate-limiting).
- **`SmsService`** : Abstraction de l'envoi de SMS (logs en local/test, prêt pour API Togocom/Moov en prod).

### 2. Repositories (`app/Repositories`)
- **`UserRepository`** : Accès aux données utilisateurs (recherche par email/phone, création auto).
- **`OtpCodeRepository`** : Gestion persistante des codes OTP (nettoyage, comptage pour rate-limit).

### 3. Controllers API (`app/Http/Controllers/Api/Auth`)
- **`OtpController`** : Routes `/otp/send` et `/otp/verify`.
- **`EmailAuthController`** : Routes `/login` et `/register`.
- **`SessionController`** : Routes `/me` (profil) et `/logout`.

## 🔒 Sécurité & Protection

- **Rate Limiting :** 
  - Global : 60 requêtes/min via middleware `throttle`.
  - OTP : Max 5 demandes par heure par numéro de téléphone.
- **Hachage :** Tous les codes OTP et mots de passe sont hachés en base de données (Bcrypt).
- **Tentatives :** Un code OTP est invalidé après 3 tentatives infructueuses.
- **Normalisation :** Les numéros de téléphone sont normalisés au format E.164 (`+228XXXXXXXX`).
- **Masquage :** Le numéro de téléphone est partiellement masqué dans les réponses API (`+228****3456`).
