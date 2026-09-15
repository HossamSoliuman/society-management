<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'society_id' => null,
            'member_id' => null,
            'unit_id' => null,
            'registration_no' => strtoupper(fake()->bothify('MH## ??####')),
            'vehicle_type' => fake()->randomElement(array_keys(Vehicle::TYPES)),
            'make' => fake()->randomElement(['Maruti', 'Hyundai', 'Honda', 'Tata', 'Bajaj']),
            'model' => fake()->word(),
            'color' => fake()->safeColorName(),
            'owner_name' => fake()->name(),
            'status' => 'active',
        ];
    }
}
