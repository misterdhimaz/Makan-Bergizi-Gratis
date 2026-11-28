<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaguParameterHistory extends Model
{
    public $timestamps = false;

    protected $table = 'pagu_parameter_histories';

    protected $fillable = [
        'recipient_category_id',
        'nilai_lama',
        'nilai_baru',
        'user_id',
        'catatan',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'nilai_lama' => 'integer',
            'nilai_baru' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $history): void {
            $history->created_at = $history->created_at ?? now();
        });
    }

    public function recipientCategory(): BelongsTo
    {
        return $this->belongsTo(RecipientCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
