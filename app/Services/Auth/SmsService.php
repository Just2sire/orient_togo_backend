<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Envoie un SMS
     *
     * En environnement de test/développement, on log juste le message.
     * En production, brancher Togocom API ou Moov API.
     */
    public function send(string $phone, string $message): void
    {
        // Mode développement/test : juste logger
        if (app()->environment(['local', 'testing'])) {
            Log::channel('sms')->info('SMS envoyé', [
                'phone' => $phone,
                'message' => $message,
                'sent_at' => now()->toIso8601String(),
            ]);

            return;
        }

        // Mode production : appeler l'API du provider (Togocom/Moov)
        // TODO: Implémenter une provider strategy pattern
        // - TogocomProvider::send()
        // - MoovProvider::send()
        // - FallbackProvider::send()

        $this->sendViaProvider($phone, $message);
    }

    /**
     * (Stub) Envoyer via le provider configuré
     */
    private function sendViaProvider(string $phone, string $message): void
    {
        // Placeholder pour la logique de production
        // Example:
        // $provider = config('services.sms.provider'); // 'togocom' | 'moov'
        // match($provider) {
        //     'togocom' => TogocomApiClient::send($phone, $message),
        //     'moov'    => MoovApiClient::send($phone, $message),
        //     default   => throw new Exception('Unknown SMS provider'),
        // };
    }

    /**
     * Pour les tests : obtenir le dernier SMS loggé
     */
    public function getLastLogged(): ?array
    {
        // Utile pour les tests : on peut vérifier que le SMS a bien été "envoyé"
        // Log::channel('sms') contient les logs
        return null; // À adapter si nécessaire
    }
}
