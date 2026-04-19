<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreTagRequest",
 *     required={["label","category","description"]},
 *
 *     @OA\Property(property="label", type="string", example="Exemple de label"),
 *     @OA\Property(property="category", type="string", example="Exemple de category"),
 *     @OA\Property(property="description", type="string", example="Exemple de description"),
 * )
 */
class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }
}

