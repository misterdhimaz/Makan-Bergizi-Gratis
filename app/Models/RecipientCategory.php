<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipientCategory extends Model
{
    use Auditable;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function auditModule(): string
    {
        return 'Data Master · Kategori Penerima';
    }

    public function locationQuotas(): HasMany
    {
        return $this->hasMany(LocationCategoryQuota::class);
    }

    public function portionStandards(): HasMany
    {
        return $this->hasMany(PortionStandard::class);
    }

    public function paguParameters(): HasMany
    {
        return $this->hasMany(PaguParameter::class);
    }
}
