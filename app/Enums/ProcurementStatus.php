<?php

namespace App\Enums;

enum ProcurementStatus: string
{
    case MENUNGGU_TERIMA = 'menunggu_terima';
    case DITERIMA_SEBAGIAN = 'diterima_sebagian';
    case DITERIMA_LENGKAP = 'diterima_lengkap';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_TERIMA => 'Belum diterima',
            self::DITERIMA_SEBAGIAN => 'Diterima sebagian',
            self::DITERIMA_LENGKAP => 'Diterima lengkap',
        };
    }
}
