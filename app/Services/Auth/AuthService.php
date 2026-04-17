<?php

namespace App\Services\Auth;

use App\Models\User;
// use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        // private OtpCodeRepositoryInterface $otpRepository,
        private OtpCodeService $otpService,
    ) {}

    /**
     * Authentifie un utilisateur avec un OTP
     * Trouve ou crée l'utilisateur si nécessaire
     * Émet un token Sanctum
     *
     * @return array{token: string, user: User, is_new: bool}
     *
     * @throws Exception
     */
    public function loginWithOtp(string $phone, string $code, ?string $deviceName = null): array
    {
        $phone = self::normalizePhone($phone);

        // Vérifier l'OTP
        $this->otpService->verify($phone, $code, type: 'login');

        // Chercher ou créer l'utilisateur
        $user = $this->userRepository->findByPhone($phone);
        $isNew = false;

        if (! $user) {
            $user = $this->userRepository->createFromOtp($phone);
            $isNew = true;
        }

        // Générer le token Sanctum
        $token = $user->createToken(
            name: $deviceName ?? 'OTP Login',
        )->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'is_new' => $isNew,
        ];
    }

    /**
     * Authentifie un utilisateur avec email + password
     * Émet un token Sanctum
     *
     * @return array{token: string, user: User, is_new: false}
     *
     * @throws Exception
     */
    public function loginWithEmail(string $email, string $password, ?string $deviceName = null): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new Exception('Identifiants invalides.');
        }

        if (! $user->is_active) {
            throw new Exception('Ce compte est désactivé.');
        }

        $token = $user->createToken(
            name: $deviceName ?? 'Email Login',
        )->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'is_new' => false,
        ];
    }

    /**
     * Inscrit un nouvel utilisateur avec email + password
     * Émet un token Sanctum
     *
     * @return array{token: string, user: User, is_new: true}
     *
     * @throws Exception
     */
    public function registerWithEmail(string $email, string $password, ?string $deviceName = null): array
    {
        // Créer l'utilisateur
        $user = $this->userRepository->createWithPassword($email, $password);

        // Générer le token Sanctum
        $token = $user->createToken(
            name: $deviceName ?? 'Email Register',
        )->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'is_new' => true,
        ];
    }

    /**
     * Déconnecte un utilisateur
     * Révoque ses token(s) Sanctum
     */
    public function logout(Request $request, bool $allDevices = false): void
    {
        if ($allDevices) {
            $request->user()->tokens()->delete();
        } else {
            $request->user()->currentAccessToken()?->delete();
        }
    }

    /**
     * Normalise un numéro de téléphone vers E.164 (+228XXXXXXXX)
     */
    public static function normalizePhone(string $phone): string
    {
        // Supprimer les espaces, tirets, parenthèses
        $phone = preg_replace('/\s|-|\(|\)/', '', $phone);

        // Si commence par +, laisser tel quel (supposé E.164)
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Si commence par 00228, remplacer par +228
        if (str_starts_with($phone, '00228')) {
            return '+'.substr($phone, 2);
        }

        // Si commence par 228, ajouter +
        if (str_starts_with($phone, '228')) {
            return '+'.$phone;
        }

        // Si commence par 2 (8 chiffres Togo), ajouter +228
        if (str_starts_with($phone, '2') && strlen($phone) === 8) {
            return '+228'.$phone;
        }

        // Sinon, on assume que c'est juste les 8 chiffres
        if (strlen($phone) === 8) {
            return '+228'.$phone;
        }

        throw new Exception('Numéro de téléphone invalide.');
    }
}
