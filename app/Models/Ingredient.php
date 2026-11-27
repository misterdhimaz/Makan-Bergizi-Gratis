<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'current_stock',
        'unit_cost',
        'supplier_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function auditModule(): string
    {
        return 'Data Master · Daftar Bahan';
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= 0;
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(DailyMenuItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
