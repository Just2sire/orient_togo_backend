<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Hash;

class OtpCodeService
{
    public function __construct(
        private OtpCodeRepositoryInterface $otpRepository,
        private SmsService $smsService,
    ) {}

    /**
     * Génère et envoie un OTP à un numéro de téléphone
     *
     * @throws Exception si le rate limit est atteint
     */
    public function send(string $phone, string $type = 'login'): void
    {
        $phone = AuthService::normalizePhone($phone);

        // Vérifier le rate limit : max 5 OTP par heure
        $recentCount = $this->otpRepository->countRecentForPhone($phone, minutes: 60);
        if ($recentCount >= 5) {
            throw new Exception('Trop de demandes d\'OTP. Réessayez dans 1 heure.');
        }

        // Invalider les anciens OTP pour ce type
        $this->otpRepository->invalidatePreviousForPhone($phone, $type);

        // Générer un code 6 chiffres
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedCode = Hash::make($code);

        // Enregistrer l'OTP
        $this->otpRepository->createForPhone(
            phone: $phone,
            hashedCode: $hashedCode,
            type: $type,
        );

        // Envoyer le SMS
        $this->smsService->send(
            phone: $phone,
            message: "Votre code de vérification OrientTogo est: {$code}. Valide 10 minutes.",
        );
    }

    /**
     * Vérifie un OTP
     *
     * @throws Exception si le code est invalide
     */
    public function verify(string $phone, string $code, string $type = 'login'): OtpCode
    {
        $phone = AuthService::normalizePhone($phone);

        // Trouver l'OTP valide (non expiré, non utilisé, pas épuisé)
        $otp = $this->otpRepository->findValidForPhone($phone, $type);

        if (! $otp) {
            throw new Exception('Aucun OTP valide trouvé pour ce numéro.');
        }

        // Incrémenter les tentatives AVANT de vérifier
        $otp->increment('attempts');

        // Vérifier le code
        if (! Hash::check($code, $otp->code)) {
            // Le code est incorrect
            if ($otp->attempts >= 3) {
                // Invalider après 3 tentatives
                $otp->update(['is_used' => true]);
                throw new Exception('Trop de tentatives. Demandez un nouveau code.');
            }
            throw new Exception('Code incorrect.');
        }

        // Code valide : marquer comme utilisé
        $otp->markAsUsed();

        return $otp;
    }
}
