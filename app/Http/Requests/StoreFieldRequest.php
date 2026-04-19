<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreFieldRequest",
 *     required={["name","description","estimated_duration_years","main_domain","is_selected"]},
 *
 *     @OA\Property(property="name", type="string", example="Exemple de name"),
 *     @OA\Property(property="description", type="string", example="Exemple de description"),
 *     @OA\Property(property="estimated_duration_years", type="number", example=99.99),
 *     @OA\Property(property="main_domain", type="string", example="Exemple de main_domain"),
 *     @OA\Property(property="is_selected", type="boolean", example=true),
 * )
 */
class StoreFieldRequest extends FormRequest
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
            'estimated_duration_years' => ['required', 'numeric'],
            'main_domain' => ['required', 'string', 'max:255'],
            'is_selected' => ['required', 'boolean'],
        ];
    }
}

