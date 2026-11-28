<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataRevision>
 */
class DataRevisionFactory extends Factory
{
    protected $model = DataRevision::class;

    public function definition(): array
    {
        return [
            'revisable_type' => DailyMenu::class,
            'revisable_id' => 1,
            'field' => 'Sayur',
            'old_values' => ['Sayur' => 'Buncis'],
            'new_values' => ['Sayur' => 'Buncis dan wortel'],
            'reason' => 'Koreksi salah catat',
            'requested_by' => User::factory(),
            'status' => 'menunggu',
        ];
    }
}
