<?php

namespace App\Enums;

enum PaguCheckResult: string
{
    case LOLOS = 'lolos';
    case PERLU_REVISI = 'perlu_revisi';

    public function label(): string
    {
        return match ($this) {
            self::LOLOS => 'Lolos PAGU',
            self::PERLU_REVISI => 'Perlu Revisi Anggaran',
        };
    }

    public function resultingMenuStatus(): MenuStatus
    {
        return match ($this) {
            self::LOLOS => MenuStatus::LOLOS_PAGU,
            self::PERLU_REVISI => MenuStatus::PERLU_REVISI_ANGGARAN,
        };
    }
}
