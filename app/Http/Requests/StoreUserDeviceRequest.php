<?php

namespace App\Http\Requests;

use App\Enums\PlatformEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "StoreUserDeviceRequest",
    required: ["push_token", "platform"],
    properties: [
        new OA\Property(property: "push_token", type: "string"),
        new OA\Property(property: "platform", type: "string", enum: ["android", "ios", "web"]),
    ]
)]
class StoreUserDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'push_token' => ['required', 'string', 'max:1000'],
            'platform'   => ['required', new Enum(PlatformEnum::class)],
        ];
    }
}

