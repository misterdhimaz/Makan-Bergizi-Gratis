<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryRoute;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryRoute>
 */
class DeliveryRouteFactory extends Factory
{
    protected $model = DeliveryRoute::class;

    public function definition(): array
    {
        return [
            'operational_date' => now('Asia/Jakarta')->toDateString(),
            'planned_departure_time' => '08:00',
            'scheduled_arrival_time' => '08:30',
            'waybill_number' => 'DST-'.now('Asia/Jakarta')->format('Ymd').'-'.fake()->unique()->numberBetween(10, 99),
            'kelompok_rute' => 'Rute 1',
            'location_id' => Location::factory(),
            'status' => DeliveryStatus::DISIAPKAN,
            'dispatched_portions' => 100,
            'confirmed_portions' => null,
            'courier_id' => User::factory(),
            'courier_name' => fake()->name(),
            'vehicle_plate' => 'BG '.fake()->numberBetween(8100, 8999).' KL',
        ];
    }
}
