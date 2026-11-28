<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'delivery_route_id',
        'status',
        'user_id',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
