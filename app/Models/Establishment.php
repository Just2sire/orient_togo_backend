<?php

namespace App\Models;

use App\Enums\EstablishmentTypeEnum;
use App\Enums\RegionEnum;
use Database\Factories\EstablishmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable([
    'name', 'slug', 'type', 'region', 'city', 'address', 'email', 'phone',
    'website', 'description', 'fees_info', 'specialties', 'is_public',
    'is_verified', 'verification_status', 'verified_by_user_id',
    'verified_at', 'is_selected', 'latitude', 'longitude',
])]
class Establishment extends Model
{
    /** @use HasFactory<EstablishmentFactory> */
    use HasFactory, HasSlug, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected function casts(): array
    {
        return [
            'type' => EstablishmentTypeEnum::class,
            'region' => RegionEnum::class,
            'specialties' => 'array',
            'is_public' => 'boolean',
            'is_verified' => 'boolean',
            'is_selected' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'verified_at' => 'datetime',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'establishment_field');
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Serie::class, 'establishment_serie');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
