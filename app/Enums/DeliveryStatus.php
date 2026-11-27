<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case DISIAPKAN = 'Disiapkan';
    case BERANGKAT = 'Berangkat';
    case TIBA = 'Tiba';
    case DITERIMA = 'Diterima';
    case BERMASALAH = 'Bermasalah';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::DITERIMA => 'success',
            self::BERMASALAH => 'danger',
            self::TIBA => 'lime',
            self::BERANGKAT => 'obsidian',
            self::DISIAPKAN => 'neutral',
        };
    }
}
