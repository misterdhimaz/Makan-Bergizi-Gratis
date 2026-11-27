<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use Auditable;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'address',
        'category_focus',
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
        return 'Data Master · Pemasok';
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
}
