<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder utama SPPG Sungai Selayur 1 (Tim REKAGIZI MBG).
     */
    public function run(): void
    {
        $this->call(RekagiziAdminSeeder::class);
    }
}
