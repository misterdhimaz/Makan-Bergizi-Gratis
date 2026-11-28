<?php

namespace Database\Factories;

use App\Models\FoodSafetyFinding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodSafetyFinding>
 */
class FoodSafetyFindingFactory extends Factory
{
    protected $model = FoodSafetyFinding::class;

    public function definition(): array
    {
        return [
            'finding_date' => now('Asia/Jakarta')->toDateString(),
            'type' => 'Suhu',
            'description' => fake()->sentence(),
            'status' => 'terbuka',
        ];
    }
}
