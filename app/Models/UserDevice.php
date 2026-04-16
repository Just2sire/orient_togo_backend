<?php

namespace App\Models;

use App\Enums\PlatformEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'push_token', 'platform', 'user_agent', 'last_active_at'])]
class UserDevice extends Model
{
    use HasFactory, HasUuids;

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
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'uuid',
            'last_active_at' => 'datetime',
            'platform' => PlatformEnum::class,
        ];
    }

    /**
     * Get the user that owns the device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function touchActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }
}
