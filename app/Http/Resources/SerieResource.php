<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SerieResource",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="code", type="string", example="D"),
 *     @OA\Property(property="label", type="string", example="SVT"),
 *     @OA\Property(property="description", type="string", example="Sciences de la Vie"),
 *     @OA\Property(property="minimum_average", type="number", example=11),
 *     @OA\Property(property="required_profile", type="string", example="Fort en SVT"),
 *     @OA\Property(property="after_bac", type="string", example="Médecine"),
 *     @OA\Property(property="tips", type="string", example="..."),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="order", type="integer", example=3),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
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
