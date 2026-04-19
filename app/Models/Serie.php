<?php

namespace App\Models;

use Database\Factories\SerieFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label', 'description', 'minimum_average', 'required_profile', 'after_bac', 'tips', 'is_active', 'order'])]
class Serie extends Model
{
    /** @use HasFactory<SerieFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * The primary key type is UUID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'tips' => 'array',
            'is_active' => 'boolean',
            'minimum_average' => 'float',
            'order' => 'integer',
        ];
    }

    public function subjectCoefficients(): HasMany
    {
        return $this->hasMany(SubjectCoefficient::class);
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_serie');
    }

    public function establishments(): BelongsToMany
    {
        return $this->belongsToMany(Establishment::class, 'establishment_serie');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
