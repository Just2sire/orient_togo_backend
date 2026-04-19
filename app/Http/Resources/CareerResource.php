<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CareerResource",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Développeur Fullstack"),
 *     @OA\Property(property="description", type="string", example="Création d'applications web..."),
 *     @OA\Property(property="required_skills", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="market_demand", type="integer", example=9),
 *     @OA\Property(property="salary_min", type="integer", example=300000),
 *     @OA\Property(property="salary_max", type="integer", example=800000),
 *     @OA\Property(property="long_description", type="string", example="..."),
 *     @OA\Property(property="is_promising", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CareerResource extends JsonResource
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
            'required_skills' => $this->required_skills,
            'market_demand' => $this->market_demand,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'long_description' => $this->long_description,
            'is_promising' => $this->is_promising,
            'growth_sector' => new GrowthSectorResource($this->whenLoaded('growthSector')),
            'sector_data' => SectorDataResource::collection($this->whenLoaded('sectorData')),
            'fields' => FieldResource::collection($this->whenLoaded('fields')),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
