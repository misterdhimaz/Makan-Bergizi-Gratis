<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DataRevision extends Model
{
    protected $fillable = [
        'revisable_type',
        'revisable_id',
        'field',
        'old_values',
        'new_values',
        'reason',
        'requested_by',
        'status',
        'approved_by',
        'decision_notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function revisable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
