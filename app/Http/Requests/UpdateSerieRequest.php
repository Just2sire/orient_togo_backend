<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSerieRequest",
 *
 *     @OA\Property(property="code", type="string", example="Exemple de code"),
 *     @OA\Property(property="label", type="string", example="Exemple de label"),
 *     @OA\Property(property="description", type="string", example="Exemple de description"),
 *     @OA\Property(property="minimum_average", type="number", example=99.99),
 *     @OA\Property(property="required_profile", type="string", example="Exemple de required_profile"),
 *     @OA\Property(property="after_bac", type="string", example="Exemple de after_bac"),
 *     @OA\Property(property="tips", type="string", example="Exemple de tips"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="order", type="integer", example=42),
 * )
 */
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

