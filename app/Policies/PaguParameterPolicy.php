<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PaguParameter;
use App\Models\User;

class PaguParameterPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::AKUNTAN, UserRole::ADMIN, UserRole::AHLI_GIZI], true);
    }

    public function view(User $user, PaguParameter $paguParameter): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::AKUNTAN, UserRole::ADMIN], true);
    }

    public function update(User $user, PaguParameter $paguParameter): bool
    {
        return in_array($user->role, [UserRole::AKUNTAN, UserRole::ADMIN], true);
    }

    public function delete(User $user, PaguParameter $paguParameter): bool
    {
        return false;
    }
}
