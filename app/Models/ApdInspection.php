<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApdInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_date',
        'shift',
        'inspector_id',
        'production_run_id',
        'total_volunteers',
        'volunteers_checked',
        'apron_compliant_count',
        'hairnet_compliant_count',
        'mask_compliant_count',
        'gloves_compliant_count',
        'safety_shoes_compliant_count',
        'clean_nails_compliant',
        'no_jewelry_compliant',
        'handwash_facility_ready',
        'compliance_percentage',
        'status',
        'notes',
        'photo_path',
        'corrective_action',
        'verified_by_head_chef_id',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'clean_nails_compliant' => 'boolean',
            'no_jewelry_compliant' => 'boolean',
            'handwash_facility_ready' => 'boolean',
            'compliance_percentage' => 'float',
            'total_volunteers' => 'integer',
            'volunteers_checked' => 'integer',
            'apron_compliant_count' => 'integer',
            'hairnet_compliant_count' => 'integer',
            'mask_compliant_count' => 'integer',
            'gloves_compliant_count' => 'integer',
            'safety_shoes_compliant_count' => 'integer',
        ];
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function productionRun(): BelongsTo
    {
        return $this->belongsTo(ProductionRun::class, 'production_run_id');
    }

    public function headChef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_head_chef_id');
    }

    public function verifiedByHeadChef(): BelongsTo
    {
        return $this->headChef();
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'patuh_penuh' => [
                'label' => 'Patuh Penuh SOP (BGN)',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
            ],
            'peringatan' => [
                'label' => 'Peringatan / Perlu Pengarahan',
                'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
            ],
            default => [
                'label' => 'Pelanggaran Kritis',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
            ],
        };
    }
}

