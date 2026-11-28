<?php

namespace App\Policies;

use App\Enums\MenuStatus;
use App\Enums\UserRole;
use App\Models\DailyMenu;
use App\Models\User;

class DailyMenuPolicy
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

    public function view(User $user, DailyMenu $dailyMenu): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::AHLI_GIZI;
    }

    public function update(User $user, DailyMenu $dailyMenu): bool
    {
        if ($user->role !== UserRole::AHLI_GIZI) {
            return false;
        }

        return in_array($dailyMenu->menuStatus(), [
            MenuStatus::DRAF,
            MenuStatus::PERLU_REVISI_ANGGARAN,
            MenuStatus::MINTA_REVISI,
        ], true);
    }

    public function delete(User $user, DailyMenu $dailyMenu): bool
    {
        return false;
    }

    public function checkPagu(User $user, DailyMenu $dailyMenu): bool
    {
        return $user->role === UserRole::AKUNTAN
            && $dailyMenu->menuStatus() === MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN;
    }

    public function approve(User $user, DailyMenu $dailyMenu): bool
    {
        return $user->role === UserRole::KEPALA_DAPUR
            && $dailyMenu->menuStatus() === MenuStatus::LOLOS_PAGU;
    }
}
