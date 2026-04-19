<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CourseResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'establishment_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', example: 'Licence Informatique'),
        new OA\Property(property: 'level', type: 'string', example: 'licence'),
        new OA\Property(property: 'description', type: 'string', example: '...'),
        new OA\Property(property: 'duration_months', type: 'integer', example: 36),
        new OA\Property(property: 'annual_fees', type: 'number', example: 50000),
        new OA\Property(property: 'accreditation', type: 'string', example: 'AMESRES'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'establishment_id' => $this->establishment_id,
            'establishment' => new EstablishmentResource($this->whenLoaded('establishment')),
            'name' => $this->name,
            'level' => $this->level,
            'description' => $this->description,
            'duration_months' => $this->duration_months,
            'annual_fees' => $this->annual_fees,
            'accreditation' => $this->accreditation,
            'careers' => CareerResource::collection($this->whenLoaded('careers')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
