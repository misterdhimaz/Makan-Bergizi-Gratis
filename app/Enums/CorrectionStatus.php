<?php

namespace App\Enums;

enum CorrectionStatus: string
{
    case MENUNGGU = 'menunggu';
    case DISETUJUI = 'disetujui';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU => 'Menunggu',
            self::DISETUJUI => 'Disetujui',
            self::DITOLAK => 'Ditolak',
        };
    }
}
