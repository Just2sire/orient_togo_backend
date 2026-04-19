<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateSectorDataRequest',
    properties: [
        new OA\Property(property: 'career_id', type: 'string', example: 'Exemple de career_id'),
        new OA\Property(property: 'growth_sector_id', type: 'string', example: 'Exemple de growth_sector_id'),
        new OA\Property(property: 'year', type: 'integer', example: 42),
        new OA\Property(property: 'average_salary', type: 'integer', example: 42),
        new OA\Property(property: 'employment_rate', type: 'number', example: 99.99),
        new OA\Property(property: 'offers_per_year', type: 'integer', example: 42),
        new OA\Property(property: 'notes', type: 'string', example: 'Exemple de notes'),
    ]
)]
class UpdateSectorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'career_id' => ['sometimes', 'string', 'max:255'],
            'growth_sector_id' => ['sometimes', 'string', 'max:255'],
            'year' => ['sometimes', 'integer'],
            'average_salary' => ['sometimes', 'integer'],
            'employment_rate' => ['sometimes', 'numeric'],
            'offers_per_year' => ['sometimes', 'integer'],
            'notes' => ['sometimes', 'string', 'max:255'],
        ];
    }
}

