<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'sequence',
        'delivery_id',
        'beneficiary_institution_id',
        'location_id',
        'reported_by',
        'category',
        'severity',
        'description',
        'photo_path',
        'status',
        'reported_at',
        'first_response_at',
        'priority_notified_at',
        'priority_recipients',
        'resolved_by',
        'resolution_notes',
        'resolution_photo_path',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'reported_at' => 'datetime',
            'first_response_at' => 'datetime',
            'priority_notified_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ComplaintHistory::class)->orderBy('created_at');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryInstitution::class, 'beneficiary_institution_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'kemasan_tumpah', 'ompreng_rusak' => 'Kemasan / Ompreng Tumpah',
            'selisih_porsi', 'porsi_kurang' => 'Selisih Jumlah Porsi',
            'keterlambatan' => 'Keterlambatan Pengiriman',
            'kualitas_organoleptik', 'basi_asam', 'benda_asing' => 'Kualitas / Uji Organoleptik',
            'kelengkapan_alat' => 'Kelengkapan Alat Makan',
            default => 'Keluhan Lainnya',
        };
    }

    public function severityBadge(): string
    {
        return match ($this->severity) {
            'kritis' => 'bg-rose-500/20 text-rose-400 border-rose-500/30 font-bold',
            'sedang' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            default => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
        };
    }
}
