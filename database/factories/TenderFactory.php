<?php

namespace Database\Factories;

use App\Models\Society;
use App\Models\Tender;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Tender>
 */
class TenderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::parse($this->faker->dateTimeBetween('-3 months', 'now'));
        $deadline = (clone $start)->addDays($this->faker->numberBetween(15, 45));
        $estimated = $this->faker->randomFloat(2, 100000, 5000000);

        return [
            'society_id' => Society::query()->value('id'),
            'title' => $this->faker->randomElement([
                'Annual Cleaning Services', 'Elevator Maintenance Contract',
                'Security Services Tender', 'Landscaping & Gardening',
                'Painting & Waterproofing Works', 'Fire Safety Equipment Supply',
                'CCTV Installation Project', 'Solar Power Installation',
            ]),
            'sub_title' => $this->faker->sentence(4),
            'reference_no' => 'TND-2025-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'department' => $this->faker->randomElement(['Maintenance', 'Security', 'Housekeeping', 'Administration', 'Facilities']),
            'tender_type' => $this->faker->randomElement(['service', 'supply']),
            'description' => $this->faker->paragraph(),
            'tender_category' => $this->faker->randomElement(['Open', 'Limited', 'Single']),
            'estimated_value' => $estimated,
            'emd_amount' => round($estimated * 0.02, 2),
            'start_date' => $start->toDateString(),
            'end_date' => $deadline->toDateString(),
            'submission_deadline' => $deadline,
            'opening_date' => (clone $deadline)->addDay(),
            'validity_days' => $this->faker->randomElement([60, 90, 120]),
            'payment_terms' => $this->faker->randomElement(['Net 30', 'Milestone based', 'On completion']),
            'delivery_terms' => $this->faker->optional()->sentence(),
            'contract_type' => $this->faker->randomElement(['Fixed', 'Rate', 'Turnkey']),
            'tax_option' => $this->faker->randomElement(['Inclusive', 'Exclusive']),
            'terms_conditions' => $this->faker->optional()->paragraph(),
            'eligibility_criteria' => $this->faker->optional()->sentence(),
            'evaluation_criteria' => $this->faker->optional()->sentence(),
            'contact_person' => $this->faker->name(),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => $this->faker->numerify('98########'),
            'venue' => $this->faker->randomElement(['Society Office', 'Clubhouse', 'Conference Room']),
            'visibility' => $this->faker->randomElement(['public', 'invited']),
            'allow_online_submission' => $this->faker->boolean(80),
            'allow_partial_bidding' => $this->faker->boolean(30),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'draft', 'under_review', 'awarded', 'closed']),
            'awarded_vendor' => null,
            'contract_value' => null,
            'awarded_date' => null,
            'closed_date' => null,
            'closed_reason' => null,
            'created_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function awarded(): static
    {
        return $this->state(fn () => [
            'status' => 'awarded',
            'awarded_vendor' => $this->faker->company(),
            'contract_value' => $this->faker->randomFloat(2, 100000, 5000000),
            'awarded_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => 'closed',
            'awarded_vendor' => $this->faker->company(),
            'contract_value' => $this->faker->randomFloat(2, 100000, 5000000),
            'closed_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'closed_reason' => $this->faker->randomElement(['Completed', 'Contract fulfilled', 'Work delivered']),
        ]);
    }
}
