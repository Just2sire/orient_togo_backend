<?php

namespace App\Http\Requests;

use App\Enums\RegionEnum;
use App\Enums\SchoolLevelEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateUserProfileRequest",
    properties: [
        new OA\Property(property: "username", type: "string", example: "jean_doe"),
        new OA\Property(property: "level", type: "string", enum: ["primary", "middle_school", "high_school", "higher_ed"]),
        new OA\Property(property: "class", type: "string", example: "Terminale D"),
        new OA\Property(property: "region", type: "string", enum: ["maritime", "plateaux", "centrale", "kara", "savanes"]),
        new OA\Property(property: "city", type: "string", example: "Lomé"),
        new OA\Property(property: "dark_mode", type: "boolean"),
        new OA\Property(property: "notifications_on", type: "boolean"),
    ]
)]
class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $profile = $this->user()->userProfile;

        return [
            'username' => [
                'sometimes', 
                'string', 
                'min:3', 
                'max:30', 
                Rule::unique('user_profiles', 'username')->ignore($profile?->id)
            ],
            'level'    => ['sometimes', new Enum(SchoolLevelEnum::class)],
            'class'    => ['sometimes', 'nullable', 'string', 'max:50'],
            'region'   => ['sometimes', new Enum(RegionEnum::class)],
            'city'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'dark_mode' => ['sometimes', 'boolean'],
            'notifications_on' => ['sometimes', 'boolean'],
            'quiz_preferences' => ['sometimes', 'array'],
        ];
    }
}
