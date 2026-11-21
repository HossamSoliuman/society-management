<?php

namespace Database\Factories;

use App\Models\MemberRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberRequest>
 */
class MemberRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'request_type' => 'join',
            'member_type' => 'owner',
            'name' => fake()->name(),
            'mobile' => '9'.fake()->numerify('#########'),
            'email' => fake()->safeEmail(),
            'unit_number' => 'A-'.fake()->numberBetween(101, 999),
            'status' => 'pending',
        ];
    }
}
