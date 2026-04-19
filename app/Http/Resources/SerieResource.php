<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SerieResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'code', type: 'string', example: 'D'),
        new OA\Property(property: 'label', type: 'string', example: 'SVT'),
        new OA\Property(property: 'description', type: 'string', example: 'Sciences de la Vie'),
        new OA\Property(property: 'minimum_average', type: 'number', example: 11),
        new OA\Property(property: 'required_profile', type: 'string', example: 'Fort en SVT'),
        new OA\Property(property: 'after_bac', type: 'string', example: 'Médecine'),
        new OA\Property(property: 'tips', type: 'string', example: '...'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'order', type: 'integer', example: 3),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class SerieResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'description' => $this->description,
            'minimum_average' => $this->minimum_average,
            'required_profile' => $this->required_profile,
            'after_bac' => $this->after_bac,
            'tips' => $this->tips,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'subject_coefficients' => SubjectCoefficientResource::collection($this->whenLoaded('subjectCoefficients')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
