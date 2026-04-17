<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Enregistre une mutation dans les audit logs.
     * Appelé automatiquement par le trait HasAuditLog.
     */
    public function record(string $action, Model $model, ?User $user = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $action !== 'created' ? $model->getOriginal() : null,
            'new_values' => $action !== 'deleted' ? $model->getAttributes() : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Retourne l'historique d'un modèle spécifique.
     *
     * @return Collection<int, AuditLog>
     */
    public function getHistory(string $modelType, string $modelId): Collection
    {
        return AuditLog::forModel($modelType, $modelId)
            ->with('user')
            ->latest('created_at')
            ->get();
    }

    /**
     * Retourne les N derniers logs d'un utilisateur.
     *
     * @return Collection<int, AuditLog>
     */
    public function getByUser(User $user, int $limit = 50): Collection
    {
        return $user->auditLogs()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
