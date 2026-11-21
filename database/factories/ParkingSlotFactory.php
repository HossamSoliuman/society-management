<?php

namespace Database\Factories;

use App\Models\ParkingSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingSlot>
 */
class ParkingSlotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'slot_number' => strtoupper(fake()->unique()->bothify('P-###')),
            'area' => 'Basement 1',
            'slot_type' => 'car',
            'is_covered' => true,
            'status' => 'available',
        ];
    }
}
