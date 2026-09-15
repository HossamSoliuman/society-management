<?php

namespace Database\Factories;

use App\Models\FamilyMember;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyMember>
 */
class FamilyMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'society_id' => null,
            'member_id' => Member::factory(),
            'name' => fake()->name(),
            'relation' => fake()->randomElement(FamilyMember::RELATIONS),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-2 years'),
            'mobile' => fake()->numerify('9#########'),
            'email' => fake()->safeEmail(),
            'is_resident' => true,
        ];
    }
}
