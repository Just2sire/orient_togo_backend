<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip_address', 'user_agent'])]
class AuditLog extends Model
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
     * Disable updated_at timestamp.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('model_type', $type)->where('model_id', $id);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
