<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateSubjectCoefficientRequest',
    properties: [
        new OA\Property(property: 'serie_id', type: 'string', example: 'Exemple de serie_id'),
        new OA\Property(property: 'subject_name', type: 'string', example: 'Exemple de subject_name'),
        new OA\Property(property: 'coefficient', type: 'integer', example: 42),
        new OA\Property(property: 'minimum_grade', type: 'number', example: 99.99),
    ]
)]
class UpdateSubjectCoefficientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'serie_id' => ['sometimes', 'string', 'max:255'],
            'subject_name' => ['sometimes', 'string', 'max:255'],
            'coefficient' => ['sometimes', 'integer'],
            'minimum_grade' => ['sometimes', 'numeric'],
        ];
    }
}

