<?php

namespace App\Models;

use App\Enums\MenuStatus;
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
        'categories_json',
        'components_json',
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
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
        'revision_reason',
        'last_note',
        'locked_at',
        'submission_cycle',
        'status_history_json',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'target_portions' => 'integer',
            'categories_json' => 'array',
            'components_json' => 'array',
            'status_history_json' => 'array',
            'calories_kcal' => 'decimal:2',
            'protein_gram' => 'decimal:2',
            'carbs_gram' => 'decimal:2',
            'fat_gram' => 'decimal:2',
            'fiber_gram' => 'decimal:2',
            'estimated_cost_per_portion' => 'decimal:2',
            'pagu_verified_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
            'submission_cycle' => 'integer',
        ];
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_accountant_id');
    }

    public function headOfKitchen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyMenuItem::class);
    }

    public function categoryPortions(): HasMany
    {
        return $this->hasMany(MenuCategoryPortion::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(MenuComponent::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(MenuHistory::class);
    }

    public function paguChecks(): HasMany
    {
        return $this->hasMany(PaguCheck::class);
    }

    public function latestPaguCheck(): HasOne
    {
        return $this->hasOne(PaguCheck::class)->latestOfMany('checked_at');
    }

    public function productionRun(): HasOne
    {
        return $this->hasOne(ProductionRun::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function menuStatus(): MenuStatus
    {
        return MenuStatus::fromLegacy((string) $this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->menuStatus()->label();
    }

    public function getDisplayStatusLabelAttribute(): string
    {
        return $this->menuStatus()->displayLabel();
    }

    public function categorySummaryLabel(): string
    {
        $names = $this->categoryPortions
            ->loadMissing('recipientCategory')
            ->pluck('recipientCategory.name')
            ->filter()
            ->values();

        if ($names->isEmpty() && is_array($this->categories_json)) {
            $names = collect($this->categories_json);
        }

        if ($names->count() >= 4) {
            return 'Semua ('.$names->count().')';
        }

        return $names->implode(' + ') ?: '—';
    }

    public function isLocked(): bool
    {
        return in_array($this->menuStatus(), [
            MenuStatus::DISETUJUI,
            MenuStatus::IN_PRODUCTION,
            MenuStatus::COMPLETED,
        ], true) || $this->locked_at !== null;
    }
}
