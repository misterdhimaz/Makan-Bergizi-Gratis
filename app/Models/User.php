<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'beneficiary_institution_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Helper RBAC untuk pengecekan satu atau beberapa role sekaligus
     */
    public function hasRole(UserRole|string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            $roleValue = $role instanceof UserRole ? $role->value : $role;
            if ($this->role->value === $roleValue) {
                return true;
            }
        }

        return false;
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryInstitution::class, 'beneficiary_institution_id');
    }

    public function createdMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class, 'created_by');
    }

    public function approvedMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class, 'approved_by');
    }

    public function verifiedMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class, 'verified_by_accountant_id');
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'courier_id');
    }

    public function complaintTickets(): HasMany
    {
        return $this->hasMany(ComplaintTicket::class, 'reported_by');
    }

    public function resolvedComplaints(): HasMany
    {
        return $this->hasMany(ComplaintTicket::class, 'resolved_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
