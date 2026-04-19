<?php

namespace App\Models;

use Database\Factories\FieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description', 'estimated_duration_years', 'main_domain', 'is_selected'])]
class Field extends Model
{
    /** @use HasFactory<FieldFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'estimated_duration_years' => 'float',
            'is_selected' => 'boolean',
        ];
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Serie::class, 'field_serie');
    }

    public function establishments(): BelongsToMany
    {
        return $this->belongsToMany(Establishment::class, 'establishment_field');
    }

    public function careers(): BelongsToMany
    {
        return $this->belongsToMany(Career::class, 'career_field');
    }

    public function growthSectors(): BelongsToMany
    {
        return $this->belongsToMany(GrowthSector::class, 'field_growth_sector');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
