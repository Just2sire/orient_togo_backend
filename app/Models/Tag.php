<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable(['label', 'slug', 'category', 'description'])]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, HasSlug, HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('label')
            ->saveSlugsTo('slug');
    }

    public function establishments(): MorphToMany
    {
        return $this->morphedByMany(Establishment::class, 'taggable');
    }

    public function careers(): MorphToMany
    {
        return $this->morphedByMany(Career::class, 'taggable');
    }

    public function fields(): MorphToMany
    {
        return $this->morphedByMany(Field::class, 'taggable');
    }
}
