<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static bool $enabled = true;

    /**
     * Daftar atribut sensitif yang dilarang masuk ke old_values / new_values.
     *
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
    ];

    /**
     * Mencatat entri audit log secara terpusat.
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
        if (! static::$enabled) {
            return null;
        }

        $user = $actor ?? Auth::user();
        $roleValue = $user?->role?->label() ?? (is_string($user?->role) ? $user->role : 'Sistem');

        return AuditLog::create([
            'occurred_at' => $occurredAt ?? now(),
            'user_id' => $user?->id,
            'user_name_snapshot' => $user?->name ?? 'Sistem',
            'role' => $roleValue,
            'action' => $action,
            'target' => $target,
            'module' => $module,
            'ip_address' => $ipAddress ?? (app()->runningInConsole() ? '127.0.0.1' : request()->ip()),
            'old_values' => self::sanitize($oldValues),
            'new_values' => self::sanitize($newValues),
            'reason' => $reason,
            'approver_id' => $approverId,
        ]);
    }

    /**
     * Menghapus seluruh key sensitif (password/hash/token) dari payload audit.
     *
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    public static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach (self::SENSITIVE_KEYS as $key) {
            unset($values[$key]);
        }

        return $values;
    }
}
