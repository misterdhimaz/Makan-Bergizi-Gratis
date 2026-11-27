<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRoute extends Model
{
    protected $fillable = [
        'operational_date',
        'waybill_number',
        'location_id',
        'status',
        'dispatched_portions',
        'confirmed_portions',
        'courier_id',
        'confirmed_by',
        'departed_at',
        'arrived_at',
        'confirmed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'status' => DeliveryStatus::class,
            'dispatched_portions' => 'integer',
            'confirmed_portions' => 'integer',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
