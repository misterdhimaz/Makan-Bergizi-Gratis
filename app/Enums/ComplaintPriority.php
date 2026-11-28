<?php

namespace App\Enums;

enum ComplaintPriority: string
{
    case NORMAL = 'normal';
    case SERIUS = 'serius';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::SERIUS => 'Serius',
        };
    }

    public static function fromLegacy(string $severity): self
    {
        return match ($severity) {
            'kritis', 'serius', 'tinggi' => self::SERIUS,
            default => self::NORMAL,
        };
    }
}
