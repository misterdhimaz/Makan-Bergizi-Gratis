<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Auditable, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'beneficiary_institution_id',
        'location_id',
        'is_active',
        'must_change_password',
        'password_expires_at',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
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
            'must_change_password' => 'boolean',
            'password_expires_at' => 'datetime',
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function auditModule(): string
    {
        return 'Pengguna & Peran';
    }

    public function auditTarget(): string
    {
        return (string) ($this->username ?: $this->email ?: $this->getKey());
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Helper RBAC untuk pengecekan satu atau beberapa role sekaligus
     */
    public function hasRole(UserRole|string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            $roleValue = $role instanceof UserRole ? $role->value : $role;
            if ($this->role->value === $roleValue || $this->role->label() === $roleValue) {
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
