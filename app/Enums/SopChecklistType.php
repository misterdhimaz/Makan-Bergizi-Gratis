<?php

namespace App\Enums;

enum SopChecklistType: string
{
    case SUHU = 'suhu';
    case KEBERSIHAN = 'kebersihan';
    case APD = 'apd';

    public function label(): string
    {
        return match ($this) {
            self::SUHU => 'Pemeriksaan Suhu',
            self::KEBERSIHAN => 'Kebersihan Dapur',
            self::APD => 'Kepatuhan APD',
        };
    }
}
