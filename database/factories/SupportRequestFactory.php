<?php

namespace Database\Factories;

use App\Models\SupportRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportRequest>
 */
class SupportRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $raisedAt = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'society_id' => null,
            'request_id' => 'PS-'.$raisedAt->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(4),
            'category' => fake()->randomElement(['Maintenance', 'Lift', 'Electrical', 'Housekeeping', 'Security', 'Garden', 'Access Control', 'Others']),
            'raised_by_type' => fake()->randomElement(['member', 'staff_admin']),
            'member_id' => null,
            'raised_by_name' => fake()->name(),
            'flat_no' => fake()->randomElement(['A-101', 'B-204', 'C-305', 'A-402']),
            'mobile' => fake()->numerify('+91 98### #####'),
            'email' => fake()->safeEmail(),
            'preferred_contact' => fake()->randomElement(['Phone', 'Email', 'WhatsApp']),
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'status' => fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']),
            'description' => fake()->paragraph(),
            'location' => fake()->randomElement(['Building A', 'Parking Area', 'Lift 2', 'Clubhouse']),
            'attachment_path' => null,
            'notes' => null,
            'raised_at' => $raisedAt,
        ];
    }
}
