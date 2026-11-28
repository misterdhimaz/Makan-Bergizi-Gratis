<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case BARU = 'baru';
    case DALAM_TINDAK_LANJUT = 'dalam_tindak_lanjut';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::BARU => 'Baru',
            self::DALAM_TINDAK_LANJUT => 'Dalam Tindak Lanjut',
            self::SELESAI => 'Selesai',
        };
    }

    public static function fromLegacy(string $value): self
    {
        return match ($value) {
            'open', 'baru' => self::BARU,
            'in_progress', 'dalam_tindak_lanjut' => self::DALAM_TINDAK_LANJUT,
            'resolved', 'selesai' => self::SELESAI,
            default => self::BARU,
        };
    }

    public function toLegacy(): string
    {
        return match ($this) {
            self::BARU => 'open',
            self::DALAM_TINDAK_LANJUT => 'in_progress',
            self::SELESAI => 'resolved',
        };
    }
}
