<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'favorable_type' => ['required', 'string', 'in:App\Models\Quiz,App\Models\Course'],
            'favorable_id'   => ['required', 'string', 'uuid'],
        ];
    }
}

