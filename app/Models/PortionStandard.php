<?php

namespace App\Models;

use App\Enums\IngredientCategory;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortionStandard extends Model
{
    use Auditable;

    protected $fillable = [
        'recipient_category_id',
        'meal_component',
        'quantity',
        'unit',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'meal_component' => IngredientCategory::class,
            'quantity' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function auditModule(): string
    {
        return 'Data Master · Standar Porsi';
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }
}
