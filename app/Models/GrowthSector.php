<?php

namespace App\Models;

use Database\Factories\GrowthSectorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'color_hex', 'icon_code', 'annual_growth', 'opportunities_description'])]
class GrowthSector extends Model
{
    /** @use HasFactory<GrowthSectorFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'annual_growth' => 'float',
        ];
    }

    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    public function sectorData(): HasMany
    {
        return $this->hasMany(SectorData::class);
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_growth_sector');
    }
}
