<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publish = Carbon::parse($this->faker->dateTimeBetween('-2 months', '+1 month'));

        return [
            'title' => $this->faker->randomElement([
                'Annual General Meeting Notice', 'Water Supply Interruption',
                'Diwali Celebration', 'Maintenance Charges Revision',
                'Fire Drill Schedule', 'Parking Rules Update',
                'Lift Maintenance Notice', 'Holi Festival Guidelines',
                'Society Audit Announcement', 'Clubhouse Renovation',
            ]),
            'notice_type' => $this->faker->randomElement(['general', 'maintenance', 'billing', 'event']),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'short_description' => $this->faker->sentence(),
            'content' => '<p>'.$this->faker->paragraph().'</p>',
            'attach_path' => null,
            'publish_at' => $publish,
            'expires_at' => (clone $publish)->addDays($this->faker->numberBetween(15, 60)),
            'pin_to_dashboard' => $this->faker->boolean(30),
            'audience_type' => $this->faker->randomElement(['all_members', 'selected_members', 'selected_units', 'selected_towers', 'custom']),
            'estimated_recipients' => $this->faker->numberBetween(20, 500),
            'send_email' => $this->faker->boolean(70),
            'send_sms' => $this->faker->boolean(40),
            'require_acknowledgement' => $this->faker->boolean(25),
            'status' => $this->faker->randomElement(['published', 'published', 'scheduled', 'draft', 'expired']),
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }
}
