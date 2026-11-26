<?php

namespace App\Services;

use App\Models\DataRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DataMasterService
{
    /**
     * Mengaktifkan atau menonaktifkan entitas Data Master (tanpa menghapus).
     */
    public function setActive(Model $model, bool $active, ?string $reason = null, ?User $actor = null): void
    {
        $old = ['is_active' => (bool) $model->getAttribute('is_active')];

        $model->forceFill(['is_active' => $active])->saveQuietly();

        AuditLogService::record(
            action: $active ? 'aktifkan' : 'nonaktifkan',
            module: method_exists($model, 'auditModule') ? $model->auditModule() : 'Data Master',
            target: (string) ($model->getAttribute('name') ?? $model->getAttribute('title') ?? $model->getAttribute('code') ?? '#'.$model->getKey()),
            oldValues: $old,
            newValues: ['is_active' => $active],
            reason: $reason,
            actor: $actor,
        );
    }

    /**
     * Mencatat revisi terstruktur ketika alasan perubahan disertakan.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function recordRevision(
        Model $model,
        array $oldValues,
        array $newValues,
        ?string $reason,
        ?User $actor = null,
    ): void {
        $cleanReason = trim((string) $reason);
        if ($cleanReason === '') {
            return;
        }

        DataRevision::create([
            'revisable_type' => $model::class,
            'revisable_id' => $model->getKey(),
            'old_values' => AuditLogService::sanitize($oldValues),
            'new_values' => AuditLogService::sanitize($newValues),
            'reason' => $cleanReason,
            'requested_by' => $actor?->id,
            'approved_by' => $actor?->id,
            'approved_at' => now(),
        ]);

        AuditLogService::record(
            action: 'revisi',
            module: method_exists($model, 'auditModule') ? $model->auditModule() : 'Data Master',
            target: (string) ($model->getAttribute('name') ?? $model->getAttribute('title') ?? $model->getAttribute('code') ?? '#'.$model->getKey()),
            oldValues: $oldValues,
            newValues: $newValues,
            reason: $cleanReason,
            actor: $actor,
            approverId: $actor?->id,
        );
    }
}
