<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionObstacle extends Model
{
    protected $fillable = [
        'operational_date',
        'daily_menu_id',
        'description',
        'status',
        'reported_by',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
        ];
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
