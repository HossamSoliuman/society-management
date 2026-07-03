<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => (string) $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->words(2, true),
            'group_id' => AccountGroup::factory(),
            'parent_id' => null,
            'type' => $this->faker->randomElement(['group', 'detail']),
            'opening_balance' => $this->faker->randomFloat(2, 0, 500000),
            'balance' => $this->faker->randomFloat(2, 0, 900000),
            'status' => 'active',
            'display_order' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
