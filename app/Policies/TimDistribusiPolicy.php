<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DeliveryDocument;
use App\Models\User;

class TimDistribusiPolicy
{
    public function updateDelivery(User $user): bool
    {
        return $user->role === UserRole::TIM_DISTRIBUSI && $user->is_active;
    }

    public function downloadDocument(User $user, DeliveryDocument $document): bool
    {
        return $user->is_active && in_array($user->role, [
            UserRole::TIM_DISTRIBUSI,
            UserRole::ASISTEN_LAPANGAN,
            UserRole::KEPALA_DAPUR,
            UserRole::ADMIN,
        ], true);
    }
}
