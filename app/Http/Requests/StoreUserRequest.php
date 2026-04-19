<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Remplacer par une Policy si besoin
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Ajoutez vos règles ici
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Le champ nom est obligatoire.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
        ];
    }
}

