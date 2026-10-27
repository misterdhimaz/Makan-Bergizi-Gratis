<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'unit',
        'current_stock',
        'min_stock_threshold',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'min_stock_threshold' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock_threshold;
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

