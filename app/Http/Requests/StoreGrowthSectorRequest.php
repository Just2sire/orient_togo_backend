<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreGrowthSectorRequest',
    required: ['name', 'description', 'color_hex', 'icon_code', 'annual_growth', 'opportunities_description'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Exemple de name'),
        new OA\Property(property: 'description', type: 'string', example: 'Exemple de description'),
        new OA\Property(property: 'color_hex', type: 'string', example: 'Exemple de color_hex'),
        new OA\Property(property: 'icon_code', type: 'string', example: 'Exemple de icon_code'),
        new OA\Property(property: 'annual_growth', type: 'number', example: 99.99),
        new OA\Property(property: 'opportunities_description', type: 'string', example: 'Exemple de opportunities_description'),
    ]
)]
class StoreGrowthSectorRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:255'],
            'color_hex' => ['required', 'string', 'max:255'],
            'icon_code' => ['required', 'string', 'max:255'],
            'annual_growth' => ['required', 'numeric'],
            'opportunities_description' => ['required', 'string', 'max:255'],
        ];
    }
}
