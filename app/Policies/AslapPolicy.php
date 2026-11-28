<?php

namespace App\Policies;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Models\DeliveryRoute;
use App\Models\InventoryTransaction;
use App\Models\User;

class AslapPolicy
{
    public function updateProcurement(User $user, InventoryTransaction $transaction): bool
    {
        return $user->role === UserRole::ASISTEN_LAPANGAN
            && $user->is_active
            && $transaction->verification_status === 'menunggu';
    }

    public function updateSchedule(User $user, DeliveryRoute $route): bool
    {
        return $user->role === UserRole::ASISTEN_LAPANGAN
            && $user->is_active
            && $route->status === DeliveryStatus::DISIAPKAN;
    }

    public function exportReport(User $user): bool
    {
        return $user->role === UserRole::ASISTEN_LAPANGAN && $user->is_active;
    }
}
