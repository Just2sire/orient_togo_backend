<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateFieldRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Exemple de name'),
        new OA\Property(property: 'description', type: 'string', example: 'Exemple de description'),
        new OA\Property(property: 'estimated_duration_years', type: 'number', example: 99.99),
        new OA\Property(property: 'main_domain', type: 'string', example: 'Exemple de main_domain'),
        new OA\Property(property: 'is_selected', type: 'boolean', example: true),
    ]
)]
class UpdateFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
            'estimated_duration_years' => ['sometimes', 'numeric'],
            'main_domain' => ['sometimes', 'string', 'max:255'],
            'is_selected' => ['sometimes', 'boolean'],
        ];
    }
}

