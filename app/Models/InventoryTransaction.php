<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'ingredient_id',
        'daily_menu_id',
        'type',
        'quantity',
        'planned_qty',
        'unit_price',
        'total_cost',
        'supplier_name',
        'invoice_photo_path',
        'freshness_condition',
        'verification_status',
        'procurement_status',
        'received_at',
        'recorded_by',
        'verified_by_accountant',
        'verified_by',
        'verified_at',
        'verification_notes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'planned_qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'received_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_accountant');
    }
}
