<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreEstablishmentRequest',
    required: ['name', 'type', 'region', 'city', 'address', 'email', 'phone', 'website', 'description', 'fees_info', 'specialties', 'is_public', 'is_verified', 'verification_status', 'is_selected', 'latitude', 'longitude'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Exemple de name'),
        new OA\Property(property: 'type', type: 'string', example: 'Exemple de type'),
        new OA\Property(property: 'region', type: 'string', example: 'Exemple de region'),
        new OA\Property(property: 'city', type: 'string', example: 'Exemple de city'),
        new OA\Property(property: 'address', type: 'string', example: 'Exemple de address'),
        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+22890123456'),
        new OA\Property(property: 'website', type: 'string', example: 'Exemple de website'),
        new OA\Property(property: 'description', type: 'string', example: 'Exemple de description'),
        new OA\Property(property: 'fees_info', type: 'string', example: 'Exemple de fees_info'),
        new OA\Property(property: 'specialties', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'is_public', type: 'boolean', example: true),
        new OA\Property(property: 'is_verified', type: 'boolean', example: true),
        new OA\Property(property: 'verification_status', type: 'string', example: 'Exemple de verification_status'),
        new OA\Property(property: 'is_selected', type: 'boolean', example: true),
        new OA\Property(property: 'latitude', type: 'number', example: 99.99),
        new OA\Property(property: 'longitude', type: 'number', example: 99.99),
    ]
)]
class StoreEstablishmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'website' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'fees_info' => ['required', 'string', 'max:255'],
            'specialties' => ['required', 'array'],
            'is_public' => ['required', 'boolean'],
            'is_verified' => ['required', 'boolean'],
            'verification_status' => ['required', 'string', 'max:255'],
            'is_selected' => ['required', 'boolean'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ];
    }
}
