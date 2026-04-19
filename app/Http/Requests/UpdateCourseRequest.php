<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCourseRequest",
 *
 *     @OA\Property(property="establishment_id", type="string", example="Exemple de establishment_id"),
 *     @OA\Property(property="name", type="string", example="Exemple de name"),
 *     @OA\Property(property="level", type="string", example="Exemple de level"),
 *     @OA\Property(property="description", type="string", example="Exemple de description"),
 *     @OA\Property(property="duration_months", type="integer", example=42),
 *     @OA\Property(property="annual_fees", type="number", example=99.99),
 *     @OA\Property(property="accreditation", type="string", example="Exemple de accreditation"),
 * )
 */
class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'establishment_id' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'level' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
            'duration_months' => ['sometimes', 'integer'],
            'annual_fees' => ['sometimes', 'numeric'],
            'accreditation' => ['sometimes', 'string', 'max:255'],
        ];
    }
}

