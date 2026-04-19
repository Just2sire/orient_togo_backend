<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateGrowthSectorRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Exemple de name'),
        new OA\Property(property: 'description', type: 'string', example: 'Exemple de description'),
        new OA\Property(property: 'color_hex', type: 'string', example: 'Exemple de color_hex'),
        new OA\Property(property: 'icon_code', type: 'string', example: 'Exemple de icon_code'),
        new OA\Property(property: 'annual_growth', type: 'number', example: 99.99),
        new OA\Property(property: 'opportunities_description', type: 'string', example: 'Exemple de opportunities_description'),
    ]
)]
class UpdateGrowthSectorRequest extends FormRequest
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
            'color_hex' => ['sometimes', 'string', 'max:255'],
            'icon_code' => ['sometimes', 'string', 'max:255'],
            'annual_growth' => ['sometimes', 'numeric'],
            'opportunities_description' => ['sometimes', 'string', 'max:255'],
        ];
    }
}

