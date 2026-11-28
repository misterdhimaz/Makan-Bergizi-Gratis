<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaguParameter extends Model
{
    use Auditable;

    protected $fillable = [
        'recipient_category_id',
        'max_budget_per_portion',
        'effective_date',
        'notes',
        'is_active',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'max_budget_per_portion' => 'integer',
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function auditModule(): string
    {
        return 'Data Master · Parameter PAGU';
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
