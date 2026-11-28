<?php

namespace App\Models;

use App\Enums\PaguCheckResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class PaguCheck extends Model
{
    protected $fillable = [
        'daily_menu_id',
        'siklus_pengajuan',
        'user_id',
        'hasil',
        'catatan',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'siklus_pengajuan' => 'integer',
            'hasil' => PaguCheckResult::class,
            'checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Hasil pemeriksaan PAGU bersifat final dan tidak dapat diubah.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Hasil pemeriksaan PAGU bersifat final dan tidak dapat dihapus.');
        });
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(PaguCheckEstimate::class);
    }
}
