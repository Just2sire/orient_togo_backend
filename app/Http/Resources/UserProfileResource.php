<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserProfileResource",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "username", type: "string"),
        new OA\Property(property: "level", type: "string"),
        new OA\Property(property: "class", type: "string", nullable: true),
        new OA\Property(property: "region", type: "string"),
        new OA\Property(property: "city", type: "string", nullable: true),
        new OA\Property(property: "dark_mode", type: "boolean"),
        new OA\Property(property: "notifications_on", type: "boolean"),
        new OA\Property(property: "onboarding_done", type: "boolean"),
    ]
)]
class UserProfileResource extends JsonResource
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
            'username' => $this->username,
            'level' => $this->level->value,
            'class' => $this->class,
            'region' => $this->region->value,
            'city' => $this->city,
            'dark_mode' => $this->dark_mode,
            'notifications_on' => $this->notifications_on,
            'onboarding_done' => $this->onboarding_done,
            'quiz_preferences' => $this->quiz_preferences,
        ];
    }
}
