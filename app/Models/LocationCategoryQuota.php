<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationCategoryQuota extends Model
{
    protected $fillable = [
        'location_id',
        'recipient_category_id',
        'portion_count',
    ];

    protected function casts(): array
    {
        return [
            'portion_count' => 'integer',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }
}
