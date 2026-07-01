<?php

namespace Database\Factories;

use App\Models\ChargeHead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChargeHead>
 */
class ChargeHeadFactory extends Factory
{
    protected $model = ChargeHead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(4),
            'category' => fake()->randomElement(['maintenance', 'utilities', 'parking', 'amenities', 'other']),
            'type' => 'recurring',
            'calculation_type' => fake()->randomElement(['per_flat', 'per_sqft', 'per_slot', 'fixed']),
            'default_amount' => fake()->randomElement([100, 150, 200, 250, 300, 400, 500, 2500]),
            'applies_to' => 'All Bills',
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'applies_to' => '—',
        ]);
    }
}
