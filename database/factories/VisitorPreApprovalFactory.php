<?php

namespace Database\Factories;

use App\Models\VisitorPreApproval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitorPreApproval>
 */
class VisitorPreApprovalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'visitor_name' => fake()->name(),
            'mobile' => '9'.fake()->numerify('#########'),
            'purpose' => 'guest',
            'people_count' => 1,
            'expected_at' => now()->addHours(2),
            'valid_until' => now()->addDay(),
            'pass_code' => fake()->numerify('######'),
            'status' => 'pending',
        ];
    }
}
