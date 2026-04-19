<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="GrowthSectorResource",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Technologie"),
 *     @OA\Property(property="description", type="string", example="..."),
 *     @OA\Property(property="color_hex", type="string", example="#0066FF"),
 *     @OA\Property(property="icon_code", type="string", example="laptop"),
 *     @OA\Property(property="annual_growth", type="number", example=12.5),
 *     @OA\Property(property="opportunities_description", type="string", example="..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class GrowthSectorResource extends JsonResource
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
            'color_hex' => $this->color_hex,
            'icon_code' => $this->icon_code,
            'annual_growth' => $this->annual_growth,
            'opportunities_description' => $this->opportunities_description,
            'careers' => CareerResource::collection($this->whenLoaded('careers')),
            'sector_data' => SectorDataResource::collection($this->whenLoaded('sectorData')),
            'fields' => FieldResource::collection($this->whenLoaded('fields')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
