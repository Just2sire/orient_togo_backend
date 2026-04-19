<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'EstablishmentResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', example: 'Exemple de name'),
        new OA\Property(property: 'type', type: 'string', example: 'Exemple de type'),
        new OA\Property(property: 'region', type: 'string', example: 'Exemple de region'),
        new OA\Property(property: 'city', type: 'string', example: 'Exemple de city'),
        new OA\Property(property: 'address', type: 'string', example: 'Exemple de address'),
        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+22890123456'),
        new OA\Property(property: 'website', type: 'string', example: 'Exemple de website'),
        new OA\Property(property: 'description', type: 'string', example: 'Exemple de description'),
        new OA\Property(property: 'fees', type: 'string', example: 'Exemple de fees'),
        new OA\Property(property: 'specialties', type: 'string', example: 'Exemple de specialties'),
        new OA\Property(property: 'is_public', type: 'boolean', example: true),
        new OA\Property(property: 'is_verified', type: 'boolean', example: true),
        new OA\Property(property: 'verification_status', type: 'string', example: 'Exemple de verification_status'),
        new OA\Property(property: 'est_selectionne', type: 'boolean', example: true),
        new OA\Property(property: 'latitude', type: 'number', example: 99.99),
        new OA\Property(property: 'longitude', type: 'number', example: 99.99),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class EstablishmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'region' => $this->region,
            'city' => $this->city,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'description' => $this->description,
            'fees' => $this->fees,
            'specialties' => $this->specialties,
            'is_public' => $this->is_public,
            'is_verified' => $this->is_verified,
            'verification_status' => $this->verification_status,
            'est_selectionne' => $this->est_selectionne,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
