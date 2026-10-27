<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_run_id',
        'inspector_id',
        'cooking_core_temp_c',
        'holding_temp_c',
        'pre_dispatch_temp_c',
        'total_kitchen_staff_present',
        'apd_mask_compliant',
        'apd_hairnet_compliant',
        'apd_gloves_compliant',
        'apd_apron_compliant',
        'apd_safety_shoes_compliant',
        'kitchen_sanitation_passed',
        'food_sample_retained',
        'food_sample_code',
        'qc_verdict',
        'corrective_action',
        'corrective_action_status',
        'resolved_by',
        'inspected_at',
    ];

    protected function casts(): array
    {
        return [
            'cooking_core_temp_c' => 'decimal:2',
            'holding_temp_c' => 'decimal:2',
            'pre_dispatch_temp_c' => 'decimal:2',
            'total_kitchen_staff_present' => 'integer',
            'apd_mask_compliant' => 'boolean',
            'apd_hairnet_compliant' => 'boolean',
            'apd_gloves_compliant' => 'boolean',
            'apd_apron_compliant' => 'boolean',
            'apd_safety_shoes_compliant' => 'boolean',
            'kitchen_sanitation_passed' => 'boolean',
            'food_sample_retained' => 'boolean',
            'inspected_at' => 'datetime',
        ];
    }

    /**
     * Persentase kepatuhan APD & Sanitasi (0 - 100%) untuk 35 Relawan
     */
    public function getApdComplianceScoreAttribute(): int
    {
        $checks = [
            $this->apd_mask_compliant,
            $this->apd_hairnet_compliant,
            $this->apd_gloves_compliant,
            $this->apd_apron_compliant,
            $this->apd_safety_shoes_compliant,
            $this->kitchen_sanitation_passed,
        ];

        $passed = count(array_filter($checks));

        return (int) round(($passed / count($checks)) * 100);
    }

    public function productionRun(): BelongsTo
    {
        return $this->belongsTo(ProductionRun::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
