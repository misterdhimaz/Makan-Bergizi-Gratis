<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'confirmed_by',
        'received_good_portions',
        'received_damaged_or_missing_portions',
        'reception_variance',
        'aroma_test',
        'taste_test',
        'color_visual_test',
        'texture_test',
        'arrival_temperature_c',
        'is_fit_for_consumption',
        'complaint_severity',
        'feedback_or_complaint',
        'reception_photo_path',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'received_good_portions' => 'integer',
            'received_damaged_or_missing_portions' => 'integer',
            'reception_variance' => 'integer',
            'arrival_temperature_c' => 'decimal:2',
            'is_fit_for_consumption' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DeliveryConfirmation $confirmation) {
            if ($confirmation->relationLoaded('delivery') || $confirmation->delivery) {
                $confirmation->reception_variance = $confirmation->received_good_portions - $confirmation->delivery->dispatched_portions;
            }
        });
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}

