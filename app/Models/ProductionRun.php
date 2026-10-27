<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_menu_id',
        'planned_portions',
        'actual_good_portions',
        'rejected_damaged_portions',
        'portion_variance',
        'cooking_started_at',
        'cooking_finished_at',
        'packaging_finished_at',
        'status',
        'supervised_by',
        'variance_reason',
        'operational_obstacle',
        'action_taken',
        'volunteers_count',
    ];

    protected function casts(): array
    {
        return [
            'planned_portions' => 'integer',
            'actual_good_portions' => 'integer',
            'rejected_damaged_portions' => 'integer',
            'portion_variance' => 'integer',
            'volunteers_count' => 'integer',
            'cooking_started_at' => 'datetime',
            'cooking_finished_at' => 'datetime',
            'packaging_finished_at' => 'datetime',
        ];
    }

    /**
     * Otomatis menghitung selisih porsi real-time setiap kali disimpan
     */
    protected static function booted(): void
    {
        static::saving(function (ProductionRun $run) {
            $run->portion_variance = $run->actual_good_portions - $run->planned_portions;
        });
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervised_by');
    }

    public function qcInspection(): HasOne
    {
        return $this->hasOne(QcInspection::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
