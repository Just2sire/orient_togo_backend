<?php

namespace App\Models;

use App\Enums\OtpTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'code', 'type', 'attempts', 'is_used', 'expires_at', 'used_at', 'ip_address'])]
class OtpCode extends Model
{
    use HasUuids;

    /**
     * Pas de updated_at — les OTPs sont immuables après création.
     */
    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OtpTypeEnum::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
            'is_used' => 'boolean',
            'attempts' => 'integer',
        ];
    }

    public function isExhausted(): bool
    {
        return $this->attempts >= 3;
    }

    /**
     * Vérifie si l'OTP est encore valide (non expiré et non utilisé).
     */
    public function isValid(): bool
    {
        return ! $this->is_used
            && ! $this->isExhausted()
            && $this->expires_at->isFuture();
    }

    /**
     * Marque l'OTP comme utilisé.
     */
    public function markAsUsed(): void
    {
        $this->update(['is_used' => true, 'used_at' => now()]);
    }
}
