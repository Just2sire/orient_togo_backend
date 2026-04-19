<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FieldResource",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Informatique"),
 *     @OA\Property(property="description", type="string", example="Développement logiciel..."),
 *     @OA\Property(property="estimated_duration_years", type="number", example=3.0),
 *     @OA\Property(property="main_domain", type="string", example="ingenierie"),
 *     @OA\Property(property="is_selected", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class FieldResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'estimated_duration_years' => $this->estimated_duration_years,
            'main_domain' => $this->main_domain,
            'is_selected' => $this->is_selected,
            'series' => SerieResource::collection($this->whenLoaded('series')),
            'establishments' => EstablishmentResource::collection($this->whenLoaded('establishments')),
            'careers' => CareerResource::collection($this->whenLoaded('careers')),
            'growth_sectors' => GrowthSectorResource::collection($this->whenLoaded('growthSectors')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
