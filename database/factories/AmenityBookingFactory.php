<?php

namespace Database\Factories;

use App\Models\AmenityBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AmenityBooking>
 */
class AmenityBookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'amenity_id' => null,
            'booking_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'guests' => 5,
            'charge' => 0,
            'status' => 'pending',
        ];
    }
}
