<?php

namespace App\Models;

use App\Enums\RegionEnum;
use App\Enums\SchoolLevelEnum;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'username', 'level', 'class', 'region', 'city', 'dark_mode', 'notifications_on', 'onboarding_done', 'quiz_preferences', 'last_level_seen'])]
class UserProfile extends Model
{
    use HasAuditLog, HasFactory, HasUuids;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => SchoolLevelEnum::class,
            'region' => RegionEnum::class,
            'last_level_seen' => SchoolLevelEnum::class,
            'dark_mode' => 'boolean',
            'notifications_on' => 'boolean',
            'onboarding_done' => 'boolean',
            'quiz_preferences' => 'array',
        ];
    }

    /**
     * Get the user that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
