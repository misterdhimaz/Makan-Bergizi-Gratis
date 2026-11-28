<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogger
{
    /**
     * Mencatat entri audit log secara terpusat (delegasi ke AuditLogService).
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public static function record(
        string $action,
        string $module,
        ?string $target = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null,
        ?User $actor = null,
        ?int $approverId = null,
        ?string $ipAddress = null,
        ?\DateTimeInterface $occurredAt = null,
    ): ?AuditLog {
        return AuditLogService::record(
            action: $action,
            module: $module,
            target: $target,
            oldValues: $oldValues,
            newValues: $newValues,
            reason: $reason,
            actor: $actor,
            approverId: $approverId,
            ipAddress: $ipAddress,
            occurredAt: $occurredAt,
        );
    }
}
