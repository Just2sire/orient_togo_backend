<?php

namespace App\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLog
{
    /**
     * Boot the trait on the model.
     */
    public static function bootHasAuditLog(): void
    {
        static::created(function (Model $model) {
            app(AuditLogService::class)->record('created', $model);
        });

        static::updated(function (Model $model) {
            app(AuditLogService::class)->record('updated', $model);
        });

        static::deleted(function (Model $model) {
            app(AuditLogService::class)->record('deleted', $model);
        });
    }

    /**
     * Get the fields that should be audited.
     */
    public function getAuditableFields(): array
    {
        if (property_exists($this, 'auditableFields')) {
            return $this->auditableFields;
        }

        return ['*'];
    }
}
