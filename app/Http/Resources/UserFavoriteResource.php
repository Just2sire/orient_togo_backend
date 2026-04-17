<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserFavoriteResource",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "favorable_type", type: "string"),
        new OA\Property(property: "favorable_id", type: "string", format: "uuid"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
    ]
)]
class UserFavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'favorable_type' => $this->favorable_type,
            'favorable_id' => $this->favorable_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
