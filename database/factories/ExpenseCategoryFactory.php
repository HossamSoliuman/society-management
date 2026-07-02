<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'society_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => fake()->randomElement(['fa-tag', 'fa-bolt', 'fa-users', 'fa-broom', 'fa-leaf']),
            'color' => fake()->randomElement(['blue', 'green', 'orange', 'purple', 'teal', 'pink', 'yellow', 'gray']),
            'description' => fake()->sentence(),
            'status' => 'active',
            'display_order' => fake()->numberBetween(1, 20),
            'applicable_for' => 'all_buildings',
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
