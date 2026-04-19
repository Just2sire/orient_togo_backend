<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreCareerRequest",
 *     required={["name","description","required_skills","market_demand","salary_min","salary_max","long_description","is_promising"]},
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
class StoreCareerRequest extends FormRequest
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
            'required_skills' => ['required', 'array'],
            'market_demand' => ['required', 'integer'],
            'salary_min' => ['required', 'integer'],
            'salary_max' => ['required', 'integer'],
            'long_description' => ['required', 'string', 'max:255'],
            'is_promising' => ['required', 'boolean'],
        ];
    }
}

