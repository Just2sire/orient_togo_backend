<?php

namespace App\Models;

use Database\Factories\CareerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'required_skills', 'market_demand', 'salary_min', 'salary_max', 'long_description', 'is_promising', 'growth_sector_id'])]
class Career extends Model
{
    /** @use HasFactory<CareerFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'market_demand' => 'integer',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'is_promising' => 'boolean',
        ];
    }

    public function growthSector(): BelongsTo
    {
        return $this->belongsTo(GrowthSector::class);
    }

    public function sectorData(): HasMany
    {
        return $this->hasMany(SectorData::class);
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'career_field');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'career_course');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
