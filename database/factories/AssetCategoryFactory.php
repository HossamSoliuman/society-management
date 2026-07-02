<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AssetCategory>
 */
class AssetCategoryFactory extends Factory
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
            'code' => Str::upper(Str::substr(Str::slug($name, ''), 0, 4)),
            'icon' => fake()->randomElement(['fa-cube', 'fa-elevator', 'fa-bolt', 'fa-shield-halved', 'fa-droplet']),
            'color' => fake()->randomElement(['blue', 'green', 'orange', 'purple', 'teal', 'red', 'gray']),
            'description' => fake()->sentence(),
            'status' => 'active',
            'display_order' => fake()->numberBetween(1, 20),
            'movable' => true,
            'immovable' => true,
            'parent_id' => null,
            'asset_life_years' => fake()->numberBetween(5, 20),
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
