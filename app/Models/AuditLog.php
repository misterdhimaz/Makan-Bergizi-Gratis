<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'occurred_at',
        'user_id',
        'user_name_snapshot',
        'role',
        'action',
        'target',
        'module',
        'ip_address',
        'old_values',
        'new_values',
        'reason',
        'approver_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $log): void {
            $log->occurred_at = $log->occurred_at ?? now();
            $log->created_at = $log->created_at ?? now();
        });

        static::updating(function (): void {
            throw new RuntimeException('Audit log bersifat immutable dan tidak dapat diubah.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Audit log bersifat immutable dan tidak dapat dihapus.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
