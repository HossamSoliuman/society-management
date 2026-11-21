<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
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
            'email' => fake()->safeEmail(),
            'occupants_count' => fake()->numberBetween(1, 5),
            'id_proof_type' => 'Aadhaar',
            'id_proof_number' => fake()->numerify('#### #### ####'),
            'move_in_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'police_verification' => 'pending',
            'status' => 'active',
        ];
    }
}
