<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SectorDataResource",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="career_id", type="string", example="Exemple de career_id"),
 *     @OA\Property(property="growth_sector_id", type="string", example="Exemple de growth_sector_id"),
 *     @OA\Property(property="year", type="integer", example=42),
 *     @OA\Property(property="average_salary", type="integer", example=42),
 *     @OA\Property(property="employment_rate", type="number", example=99.99),
 *     @OA\Property(property="offers_per_year", type="integer", example=42),
 *     @OA\Property(property="notes", type="string", example="Exemple de notes"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SectorDataResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'career_id' => $this->career_id,
            'growth_sector_id' => $this->growth_sector_id,
            'year' => $this->year,
            'average_salary' => $this->average_salary,
            'employment_rate' => $this->employment_rate,
            'offers_per_year' => $this->offers_per_year,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
