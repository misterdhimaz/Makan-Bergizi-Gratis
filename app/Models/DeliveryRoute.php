<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryRoute extends Model
{
    protected $fillable = [
        'operational_date',
        'planned_departure_time',
        'scheduled_arrival_time',
        'waybill_number',
        'kelompok_rute',
        'location_id',
        'status',
        'dispatched_portions',
        'confirmed_portions',
        'courier_id',
        'courier_name',
        'vehicle_plate',
        'confirmed_by',
        'prepared_at',
        'departed_at',
        'arrived_at',
        'confirmed_at',
        'notes',
        'nama_penerima_serah',
        'jumlah_diserahkan',
        'catatan_lapangan',
        'packaging_condition',
        'organoleptic_taste',
        'organoleptic_aroma',
        'organoleptic_texture',
        'organoleptic_color',
        'organoleptic_status',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'status' => DeliveryStatus::class,
            'dispatched_portions' => 'integer',
            'confirmed_portions' => 'integer',
            'prepared_at' => 'datetime',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function delayMinutes(): int
    {
        if (! $this->arrived_at) {
            return 0;
        }

        $scheduled = $this->operational_date
            ? $this->operational_date->copy()->setTimeFromTimeString(($this->scheduled_arrival_time ?: '08:00').':00')
            : $this->arrived_at->copy()->setTime(8, 0, 0);

        $arrived = $this->arrived_at->copy()->timezone('Asia/Jakarta');
        $scheduledJakarta = $scheduled->copy()->shiftTimezone('Asia/Jakarta');

        $diff = (int) $scheduledJakarta->diffInMinutes($arrived, false);

        return max(0, $diff);
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

    public function statusLogs(): HasMany
    {
        return $this->hasMany(DeliveryStatusLog::class)->orderBy('created_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DeliveryDocument::class)->latest();
    }
}
