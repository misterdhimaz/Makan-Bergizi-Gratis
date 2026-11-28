<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ComplaintTicket;
use App\Models\User;

class KepalaDapurPolicy
{
    public function decideMenu(User $user): bool
    {
        return $user->role === UserRole::KEPALA_DAPUR && $user->is_active;
    }

    public function decideRevision(User $user): bool
    {
        return $user->role === UserRole::KEPALA_DAPUR && $user->is_active;
    }

    public function resolveFinding(User $user): bool
    {
        return $user->role === UserRole::KEPALA_DAPUR && $user->is_active;
    }

    public function handleComplaint(User $user, ComplaintTicket $ticket): bool
    {
        return $user->role === UserRole::KEPALA_DAPUR
            && $user->is_active
            && in_array($ticket->severity, ['kritis', 'serius'], true);
    }

    public function exportReport(User $user): bool
    {
        return $user->role === UserRole::KEPALA_DAPUR && $user->is_active;
    }
}
