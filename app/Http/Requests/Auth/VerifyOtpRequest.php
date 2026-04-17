<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'phone'       => ['required', 'string', 'regex:/^(\+?228)?[0-9]{8}$/'],
            'code'        => ['required', 'digits:6'],
            'device_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'platform'    => ['sometimes', 'nullable', 'in:android,ios,web'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.digits' => 'Le code OTP doit contenir exactement 6 chiffres.',
        ];
    }
}
