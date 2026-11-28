<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodSafetyFinding extends Model
{
    protected $fillable = [
        'food_safety_check_id',
        'finding_date',
        'type',
        'description',
        'status',
        'handled_by',
        'handler_note',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'finding_date' => 'date',
            'handled_at' => 'datetime',
        ];
    }

    public function foodSafetyCheck(): BelongsTo
    {
        return $this->belongsTo(FoodSafetyCheck::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
