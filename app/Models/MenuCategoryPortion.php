<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuCategoryPortion extends Model
{
    protected $fillable = [
        'daily_menu_id',
        'recipient_category_id',
        'portions',
    ];

    protected function casts(): array
    {
        return [
            'portions' => 'integer',
        ];
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }
}
