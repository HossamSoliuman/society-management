<?php

namespace Database\Factories;

use App\Models\AmcCategory;
use App\Models\AmcContract;
use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<AmcContract>
 */
class AmcContractFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::parse($this->faker->dateTimeBetween('-18 months', '-2 months'));
        $duration = $this->faker->randomElement([6, 12, 24]);
        $end = (clone $start)->addMonths($duration);

        return [
            'society_id' => Society::query()->value('id'),
            'item_asset' => $this->faker->randomElement([
                'Elevator AMC', 'Generator AMC', 'Fire Safety AMC', 'HVAC AMC',
                'Water Pump AMC', 'CCTV AMC', 'STP AMC', 'Solar Panel AMC',
            ]),
            'amc_category_id' => AmcCategory::query()->inRandomOrder()->value('id'),
            'vendor_name' => $this->faker->company(),
            'service_vendor_id' => null,
            'contract_no' => 'AMC-'.$this->faker->unique()->numerify('2025-####'),
            'po_invoice_no' => 'PO-'.$this->faker->numerify('#####'),
            'contract_type' => $this->faker->randomElement(['Comprehensive', 'Non-Comprehensive']),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'duration_months' => $duration,
            'amount' => $this->faker->randomFloat(2, 15000, 250000),
            'tax_percent' => $this->faker->randomElement([0, 5, 12, 18]),
            'renewal_reminder_days' => $this->faker->randomElement([15, 30, 45]),
            'description' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['active', 'active', 'expiring_soon', 'expired', 'draft']),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => 'expired']);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }
}
