<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'waybill_number',
        'production_run_id',
        'beneficiary_institution_id',
        'courier_id',
        'vehicle_plate_number',
        'allocated_portions',
        'dispatched_portions',
        'departed_at',
        'arrived_at',
        'dispatch_photo_path',
        'signed_waybill_path',
        'status',
        'courier_notes',
    ];

    protected function casts(): array
    {
        return [
            'allocated_portions' => 'integer',
            'dispatched_portions' => 'integer',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
        ];
    }

    public function productionRun(): BelongsTo
    {
        return $this->belongsTo(ProductionRun::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryInstitution::class, 'beneficiary_institution_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function confirmation(): HasOne
    {
        return $this->hasOne(DeliveryConfirmation::class);
    }

    public function complaintTickets(): HasMany
    {
        return $this->hasMany(ComplaintTicket::class);
    }
}
