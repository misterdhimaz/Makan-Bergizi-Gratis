<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Location extends Model
{
    use Auditable;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'target_portions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_portions' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function auditModule(): string
    {
        return 'Data Master · Lokasi Penerima';
    }

    public function categoryQuotas(): HasMany
    {
        return $this->hasMany(LocationCategoryQuota::class);
    }

    public function deliveryRoutes(): HasMany
    {
        return $this->hasMany(DeliveryRoute::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'location_id');
    }

    public function activeGuruPic(): HasOne
    {
        return $this->hasOne(User::class, 'location_id')
            ->where('role', UserRole::GURU_PIC->value)
            ->where('is_active', true);
    }
}
