<?php

namespace Database\Factories;

use App\Models\ParkingAllotment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingAllotment>
 */
class ParkingAllotmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'parking_slot_id' => null,
            'start_date' => now()->subMonth(),
            'monthly_charge' => 0,
            'status' => 'active',
        ];
    }
}
