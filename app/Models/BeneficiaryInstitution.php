<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeneficiaryInstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'recipient_category',
        'default_quota',
        'address',
        'latitude',
        'longitude',
        'contact_phone',
        'pic_name',
        'pic_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_quota' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function picUsers(): HasMany
    {
        return $this->hasMany(User::class, 'beneficiary_institution_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'beneficiary_institution_id');
    }

    public function complaintTickets(): HasMany
    {
        return $this->hasMany(ComplaintTicket::class, 'beneficiary_institution_id');
    }

    public function recipientCategoryLabel(): string
    {
        return match ($this->recipient_category) {
            'siswa' => 'Siswa / Santri Sekolah',
            'balita_bumil' => 'Balita & Ibu Hamil',
            'balita_busui' => 'Balita & Ibu Menyusui',
            'balita' => 'Balita',
            'ibu_hamil' => 'Ibu Hamil',
            'ibu_menyusui' => 'Ibu Menyusui',
            default => 'Umum',
        };
    }
}
