<?php

namespace Database\Factories;

use App\Models\AmcCategory;
use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AmcCategory>
 */
class AmcCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Elevators', 'Generators', 'Fire Safety', 'HVAC', 'Water Pumps',
            'CCTV & Surveillance', 'Solar Systems', 'STP & Water Treatment',
            'Electrical', 'Landscaping', 'Intercom', 'Access Control',
        ]);

        return [
            'society_id' => Society::query()->value('id'),
            'name' => $name,
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->randomElement(['fa-elevator', 'fa-bolt', 'fa-fire-extinguisher', 'fa-fan', 'fa-faucet', 'fa-video']),
            'assets_covered' => $this->faker->numberBetween(2, 30),
            'applicable_assets' => $this->faker->randomElements(['Tower A', 'Tower B', 'Tower C', 'Clubhouse', 'Basement'], 2),
            'default_reminder_days' => $this->faker->randomElement([15, 30, 45, 60]),
            'default_duration_months' => $this->faker->randomElement([6, 12, 24]),
            'tax_applicable' => $this->faker->boolean(70),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
