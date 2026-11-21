<?php

namespace Database\Factories;

use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'name' => fake()->randomElement(['Clubhouse', 'Party Hall', 'Tennis Court', 'Guest Room']),
            'open_time' => '06:00',
            'close_time' => '22:00',
            'max_hours' => 4,
            'charge_per_hour' => 0,
            'requires_approval' => true,
            'status' => 'active',
        ];
    }
}
