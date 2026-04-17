<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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

    /**
     * Masque le numéro de téléphone pour la confidentialité.
     */
    private function maskPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        
        // Format Togo +228 XX XX XX XX -> +228 **** XX XX
        if (strlen($phone) >= 12) {
            return substr($phone, 0, 4) . '****' . substr($phone, -4);
        }

        return $phone;
    }
}
