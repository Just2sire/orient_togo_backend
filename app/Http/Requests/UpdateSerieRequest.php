<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateSerieRequest',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'Exemple de code'),
        new OA\Property(property: 'label', type: 'string', example: 'Exemple de label'),
        new OA\Property(property: 'description', type: 'string', example: 'Exemple de description'),
        new OA\Property(property: 'minimum_average', type: 'number', example: 99.99),
        new OA\Property(property: 'required_profile', type: 'string', example: 'Exemple de required_profile'),
        new OA\Property(property: 'after_bac', type: 'string', example: 'Exemple de after_bac'),
        new OA\Property(property: 'tips', type: 'string', example: 'Exemple de tips'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'order', type: 'integer', example: 42),
    ]
)]
class UpdateSerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:10'],
            'label' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'minimum_average' => ['sometimes', 'numeric', 'between:0,20'],
            'required_profile' => ['sometimes', 'nullable', 'string'],
            'after_bac' => ['sometimes', 'nullable', 'string'],
            'tips' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer'],
        ];
    }
}

