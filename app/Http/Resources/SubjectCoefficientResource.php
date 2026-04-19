<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SubjectCoefficientResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'serie_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'subject_name', type: 'string', example: 'Mathématiques'),
        new OA\Property(property: 'coefficient', type: 'integer', example: 4),
        new OA\Property(property: 'minimum_grade', type: 'number', example: 10),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class SubjectCoefficientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'serie_id' => $this->serie_id,
            'subject_name' => $this->subject_name,
            'coefficient' => $this->coefficient,
            'minimum_grade' => $this->minimum_grade,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
