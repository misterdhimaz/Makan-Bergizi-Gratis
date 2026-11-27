<?php

namespace App\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            if (! AuditLogService::$enabled) {
                return;
            }

            AuditLogService::record(
                action: 'tambah',
                module: method_exists($model, 'auditModule') ? $model->auditModule() : class_basename($model),
                target: method_exists($model, 'auditTarget') ? $model->auditTarget() : (string) ($model->name ?? $model->title ?? $model->code ?? $model->getKey()),
                oldValues: null,
                newValues: AuditLogService::sanitize($model->getAttributes()),
            );
        });

        static::updated(function (Model $model): void {
            if (! AuditLogService::$enabled) {
                return;
            }

            $dirty = AuditLogService::sanitize($model->getDirty()) ?? [];
            unset($dirty['updated_at'], $dirty['last_login_at'], $dirty['failed_login_attempts'], $dirty['locked_until']);

            if ($dirty === []) {
                return;
            }

            $old = [];
            foreach (array_keys($dirty) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            AuditLogService::record(
                action: 'ubah',
                module: method_exists($model, 'auditModule') ? $model->auditModule() : class_basename($model),
                target: method_exists($model, 'auditTarget') ? $model->auditTarget() : (string) ($model->name ?? $model->title ?? $model->code ?? $model->getKey()),
                oldValues: AuditLogService::sanitize($old),
                newValues: AuditLogService::sanitize($dirty),
            );
        });
    }
}
