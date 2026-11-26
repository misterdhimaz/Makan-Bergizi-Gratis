<?php

namespace App\Enums;

enum IngredientCategory: string
{
    case POKOK = 'Pokok';
    case LAUK_HEWANI = 'Lauk hewani';
    case LAUK_NABATI = 'Lauk nabati';
    case SAYUR = 'Sayur';
    case MINUMAN = 'Minuman';

    public function label(): string
    {
        return $this->value;
    }
}
