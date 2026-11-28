<?php

namespace App\Support;

use Carbon\CarbonInterface;

class IdFormat
{
    public static function number(int|float|null $value): string
    {
        if ($value === null) {
            return '0';
        }

        return number_format((float) $value, 0, ',', '.');
    }

    public static function rupiah(int|float|null $value): string
    {
        if ($value === null) {
            return 'Rp —';
        }

        return 'Rp '.self::number($value);
    }

    public static function dateLong(CarbonInterface $date): string
    {
        return $date->timezone('Asia/Jakarta')->locale('id')->translatedFormat('l, j M Y');
    }

    public static function dateShort(CarbonInterface $date): string
    {
        return $date->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y');
    }

    public static function dateTimeShort(CarbonInterface $date): string
    {
        return $date->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y · H:i').' WIB';
    }

    public static function greeting(?CarbonInterface $now = null): string
    {
        $hour = ($now ?? now('Asia/Jakarta'))->timezone('Asia/Jakarta')->hour;

        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
