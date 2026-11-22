<?php

namespace Database\Factories;

use App\Models\DemoRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoRequest>
 */
class DemoRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'society_name' => fake()->lastName().' Heights CHS',
            'city' => fake()->randomElement(['Mumbai', 'Pune', 'Bengaluru', 'Hyderabad', 'Ahmedabad']),
            'units' => fake()->numberBetween(24, 600),
            'role' => fake()->randomElement(array_keys(DemoRequest::ROLES)),
            'phone' => '9'.fake()->numerify('#########'),
            'email' => fake()->safeEmail(),
            'message' => fake()->optional()->sentence(),
            'status' => 'new',
        ];
    }
}
