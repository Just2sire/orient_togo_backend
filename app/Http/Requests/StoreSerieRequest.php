<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "StoreSerieRequest",
    required: ["code", "label", "description", "minimum_average", "is_active", "order"],
    properties: [
        new OA\Property(property: "code", type: "string", example: "D"),
        new OA\Property(property: "label", type: "string", example: "SVT"),
        new OA\Property(property: "description", type: "string", example: "Sciences de la Vie"),
        new OA\Property(property: "minimum_average", type: "number", example: 11),
        new OA\Property(property: "required_profile", type: "string", example: "Fort en SVT"),
        new OA\Property(property: "after_bac", type: "string", example: "Médecine"),
        new OA\Property(property: "tips", type: "array", items: new OA\Items(type: "string")),
        new OA\Property(property: "is_active", type: "boolean", example: true),
        new OA\Property(property: "order", type: "integer", example: 42),
    ]
)]
class StoreSerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>|string> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'minimum_average' => ['required', 'numeric', 'between:0,20'],
            'required_profile' => ['nullable', 'string'],
            'after_bac' => ['nullable', 'string'],
            'tips' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer'],
        ];
    }
}

