<?php

namespace Database\Factories;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'name' => fake()->name(),
            'role' => fake()->randomElement(Staff::ROLES),
            'employment_type' => 'society',
            'shift' => 'general',
            'mobile' => '9'.fake()->numerify('#########'),
            'joining_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
            'monthly_salary' => fake()->randomElement([14000, 16000, 18000, 22000]),
            'status' => 'active',
        ];
    }
}
