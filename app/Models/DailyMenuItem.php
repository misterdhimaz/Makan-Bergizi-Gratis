<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_menu_id',
        'ingredient_id',
        'qty_per_portion',
        'total_planned_qty',
        'actual_used_qty',
        'preparation_notes',
    ];

    protected function casts(): array
    {
        return [
            'qty_per_portion' => 'decimal:4',
            'total_planned_qty' => 'decimal:2',
            'actual_used_qty' => 'decimal:2',
        ];
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}

