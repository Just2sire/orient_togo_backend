<?php

namespace App\Models;

use App\Enums\LevelEnum;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['establishment_id', 'name', 'level', 'description', 'duration_months', 'annual_fees', 'accreditation'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'level' => LevelEnum::class,
            'duration_months' => 'integer',
            'annual_fees' => 'float',
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function careers(): BelongsToMany
    {
        return $this->belongsToMany(Career::class, 'career_course');
    }
}
