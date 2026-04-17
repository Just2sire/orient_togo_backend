<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
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
            'phone' => ['required', 'string', 'regex:/^(\+?228)?[0-9]{8}$/'],
            'type'  => ['sometimes', 'in:login,register'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Le format du numéro de téléphone est invalide (8 chiffres attendus, ex: 90010203).',
            'type.in'     => 'Le type d\'OTP doit être login ou register.',
        ];
    }
}
