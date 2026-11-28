<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'daily_menu_id',
        'aksi',
        'user_id',
        'catatan',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $history): void {
            $history->created_at = $history->created_at ?? now();
        });
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
