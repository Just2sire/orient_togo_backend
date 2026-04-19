<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSubjectCoefficientRequest",
 *     required={["serie_id","subject_name","coefficient","minimum_grade"]},
 *
 *     @OA\Property(property="serie_id", type="string", example="Exemple de serie_id"),
 *     @OA\Property(property="subject_name", type="string", example="Exemple de subject_name"),
 *     @OA\Property(property="coefficient", type="integer", example=42),
 *     @OA\Property(property="minimum_grade", type="number", example=99.99),
 * )
 */
class StoreSubjectCoefficientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'serie_id' => ['required', 'string', 'max:255'],
            'subject_name' => ['required', 'string', 'max:255'],
            'coefficient' => ['required', 'integer'],
            'minimum_grade' => ['required', 'numeric'],
        ];
    }
}

