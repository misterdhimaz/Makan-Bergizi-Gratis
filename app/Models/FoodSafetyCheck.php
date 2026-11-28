<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodSafetyCheck extends Model
{
    protected $fillable = [
        'check_date',
        'shift',
        'temperatures_json',
        'hygiene_json',
        'apd_json',
        'total_volunteers',
        'compliant_volunteers',
        'attachment_path',
        'notes',
        'inspector_id',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
            'temperatures_json' => 'array',
            'hygiene_json' => 'array',
            'apd_json' => 'array',
            'total_volunteers' => 'integer',
            'compliant_volunteers' => 'integer',
            'checked_at' => 'datetime',
        ];
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function compliancePercent(): string
    {
        if ($this->total_volunteers <= 0) {
            return '0%';
        }

        $pct = ($this->compliant_volunteers / $this->total_volunteers) * 100;

        return abs($pct - round($pct)) < 0.05
            ? number_format($pct, 0, ',', '.').'%'
            : number_format($pct, 1, ',', '.').'%';
    }
}
