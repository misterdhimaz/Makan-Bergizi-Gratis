<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'delivery_id',
        'beneficiary_institution_id',
        'reported_by',
        'category',
        'severity',
        'description',
        'photo_path',
        'status',
        'resolved_by',
        'resolution_notes',
        'resolution_photo_path',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
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
            'porsi_kurang' => 'Kekurangan Porsi / Tidak Cukup',
            'basi_asam' => 'Makanan Basi / Berbau Asam',
            'benda_asing' => 'Temuan Benda Asing',
            'keterlambatan' => 'Keterlambatan Jam Tiba (>10:30 WIB)',
            'ompreng_rusak' => 'Ompreng Rusak / Tumpah',
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

