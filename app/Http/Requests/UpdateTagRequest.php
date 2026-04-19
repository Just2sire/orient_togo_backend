<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateTagRequest",
 *
 *     @OA\Property(property="label", type="string", example="Exemple de label"),
 *     @OA\Property(property="category", type="string", example="Exemple de category"),
 *     @OA\Property(property="description", type="string", example="Exemple de description"),
 * )
 */
class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
        ];
    }
}

