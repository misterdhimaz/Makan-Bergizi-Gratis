<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaguCheckEstimate extends Model
{
    protected $fillable = [
        'pagu_check_id',
        'recipient_category_id',
        'estimasi_per_porsi',
        'pagu_saat_itu',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_per_porsi' => 'integer',
            'pagu_saat_itu' => 'integer',
        ];
    }

    public function paguCheck(): BelongsTo
    {
        return $this->belongsTo(PaguCheck::class);
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }

    public function exceedsPagu(): bool
    {
        return $this->estimasi_per_porsi !== null
            && $this->estimasi_per_porsi > $this->pagu_saat_itu;
    }

    public function difference(): ?int
    {
        if ($this->estimasi_per_porsi === null) {
            return null;
        }

        return $this->estimasi_per_porsi - $this->pagu_saat_itu;
    }
}
