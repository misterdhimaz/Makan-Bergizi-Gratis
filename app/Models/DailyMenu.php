<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'operational_date',
        'menu_code',
        'menu_name',
        'target_portions',
        'recipient_category',
        'calories_kcal',
        'protein_gram',
        'carbs_gram',
        'fat_gram',
        'fiber_gram',
        'estimated_cost_per_portion',
        'pagu_status',
        'pagu_notes',
        'verified_by_accountant_id',
        'pagu_verified_at',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'target_portions' => 'integer',
            'calories_kcal' => 'decimal:2',
            'protein_gram' => 'decimal:2',
            'carbs_gram' => 'decimal:2',
            'fat_gram' => 'decimal:2',
            'fiber_gram' => 'decimal:2',
            'estimated_cost_per_portion' => 'decimal:2',
            'pagu_verified_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_accountant_id');
    }

    public function headOfKitchen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyMenuItem::class);
    }

    public function productionRun(): HasOne
    {
        return $this->hasOne(ProductionRun::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draf Rencana Ahli Gizi',
            'pending_budget_review' => 'Menunggu Validasi PAGU Akuntan',
            'pending_approval' => 'Menunggu Persetujuan Kepala Dapur',
            'approved' => 'Disetujui Kepala Dapur',
            'in_production' => 'Dalam Siklus Pengolahan Dapur',
            'completed' => 'Distribusi Selesai',
            'revision_needed' => 'Perlu Revisi Anggaran/Menu',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
