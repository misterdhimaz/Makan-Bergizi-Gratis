<?php

namespace App\Models;

use App\Enums\SopChecklistType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SopChecklist extends Model
{
    use Auditable;

    protected $fillable = [
        'code',
        'type',
        'title',
        'threshold_value',
        'frequency',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => SopChecklistType::class,
            'is_active' => 'boolean',
        ];
    }

    public function auditModule(): string
    {
        return 'Data Master · Checklist SOP BGN';
    }
}
