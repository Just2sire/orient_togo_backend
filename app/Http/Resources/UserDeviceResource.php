<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserDeviceResource",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "push_token", type: "string"),
        new OA\Property(property: "platform", type: "string"),
        new OA\Property(property: "last_active_at", type: "string", format: "date-time"),
    ]
)]
class UserDeviceResource extends JsonResource
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
            'push_token' => $this->push_token,
            'platform' => $this->platform->value,
            'last_active_at' => $this->last_active_at?->toIso8601String(),
        ];
    }
}
