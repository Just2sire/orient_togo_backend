<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoleEnum;
use App\Models\Scopes\ActiveScope;
use App\Traits\HasAuditLog;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['email', 'phone', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
#[ScopedBy([ActiveScope::class])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasAuditLog, HasFactory, HasRoles, HasUuids, Notifiable;

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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRoleEnum::class,
        ];
    }

    /**
     * Get the user's role.
     */
    public function getRoleAttribute($value): UserRoleEnum
    {
        return UserRoleEnum::from($value);
    }

    /**
     * Set the user's role.
     */
    public function setRoleAttribute(UserRoleEnum $value): void
    {
        $this->attributes['role'] = $value->value;
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function userFavorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function userDevices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class, 'phone', 'phone');
    }

    public function sessionQuizzes()
    {
        // TODO: Will be implemented in Phase B.
        // return $this->hasMany(SessionQuiz::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, UserRoleEnum $role)
    {
        return $query->where('role', $role->value);
    }
}
