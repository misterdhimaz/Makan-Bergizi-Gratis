<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PaguCheck;
use App\Models\User;

class PaguCheckPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::AKUNTAN,
            UserRole::AHLI_GIZI,
            UserRole::KEPALA_DAPUR,
            UserRole::ADMIN,
        ], true);
    }

    public function view(User $user, PaguCheck $paguCheck): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::AKUNTAN;
    }

    public function update(User $user, PaguCheck $paguCheck): bool
    {
        return false;
    }

    public function delete(User $user, PaguCheck $paguCheck): bool
    {
        return false;
    }
}
