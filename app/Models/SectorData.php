<?php

namespace App\Models;

use Database\Factories\SectorDataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['career_id', 'growth_sector_id', 'year', 'average_salary', 'employment_rate', 'offers_per_year', 'notes'])]
class SectorData extends Model
{
    /** @use HasFactory<SectorDataFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'average_salary' => 'integer',
            'employment_rate' => 'float',
            'offers_per_year' => 'integer',
        ];
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function growthSector(): BelongsTo
    {
        return $this->belongsTo(GrowthSector::class);
    }
}
