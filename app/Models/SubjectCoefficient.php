<?php

namespace App\Models;

use Database\Factories\SubjectCoefficientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['serie_id', 'subject_name', 'coefficient', 'minimum_grade'])]
class SubjectCoefficient extends Model
{
    /** @use HasFactory<SubjectCoefficientFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'coefficient' => 'integer',
            'minimum_grade' => 'float',
        ];
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }
}
