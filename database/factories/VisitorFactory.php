<?php

namespace Database\Factories;

use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'name' => fake()->name(),
            'mobile' => '9'.fake()->numerify('#########'),
            'purpose' => fake()->randomElement(array_keys(Visitor::PURPOSES)),
            'people_count' => 1,
            'check_in_at' => now()->subHour(),
        ];
    }
}
