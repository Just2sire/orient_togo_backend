<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCareerRequest",
 *
 *     @OA\Property(property="name", type="string", example="Exemple de name"),
 *     @OA\Property(property="description", type="string", example="Exemple de description"),
 *     @OA\Property(property="required_skills", type="string", example="Exemple de required_skills"),
 *     @OA\Property(property="market_demand", type="integer", example=42),
 *     @OA\Property(property="salary_min", type="integer", example=42),
 *     @OA\Property(property="salary_max", type="integer", example=42),
 *     @OA\Property(property="long_description", type="string", example="Exemple de long_description"),
 *     @OA\Property(property="is_promising", type="boolean", example=true),
 * )
 */
class UpdateCareerRequest extends FormRequest
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
            'required_skills' => ['sometimes', 'array'],
            'market_demand' => ['sometimes', 'integer'],
            'salary_min' => ['sometimes', 'integer'],
            'salary_max' => ['sometimes', 'integer'],
            'long_description' => ['sometimes', 'string', 'max:255'],
            'is_promising' => ['sometimes', 'boolean'],
        ];
    }
}

