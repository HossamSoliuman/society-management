<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $name = fake()->name();
        $tower = fake()->randomElement(['Tower A', 'Tower B', 'Tower C', 'Tower D']);
        $flat = substr($tower, -1).'-'.fake()->numberBetween(1, 9).fake()->numerify('0#');

        return [
            'name' => $name,
            'member_type' => fake()->randomElement(['owner', 'family_member', 'tenant']),
            'flat_unit' => $flat,
            'tower_wing' => $tower,
            'mobile' => '98765'.fake()->numerify('#####'),
            'email' => fake()->unique()->safeEmail(),
            'status' => 'active',
            'avatar' => null,
            'join_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'blocked']);
    }
}
