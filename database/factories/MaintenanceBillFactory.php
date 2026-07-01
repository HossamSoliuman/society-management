<?php

namespace Database\Factories;

use App\Models\MaintenanceBill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<MaintenanceBill>
 */
class MaintenanceBillFactory extends Factory
{
    protected $model = MaintenanceBill::class;

    public function definition(): array
    {
        $billDate = Carbon::parse(fake()->dateTimeBetween('-6 months', 'now'));
        $subTotal = fake()->randomElement([3100, 3300, 3500]);
        $status = fake()->randomElement(['paid', 'pending', 'partial', 'overdue']);

        $collected = match ($status) {
            'paid' => $subTotal,
            'partial' => (int) round($subTotal * fake()->randomFloat(2, 0.2, 0.6)),
            default => 0,
        };

        return [
            'bill_number' => 'MB-'.$billDate->format('Y').'-'.fake()->unique()->numerify('000###'),
            'member_name' => fake()->name(),
            'flat_number' => strtoupper(fake()->randomLetter()).'-'.fake()->numberBetween(101, 904),
            'tower_wing' => 'Tower '.fake()->randomElement(['A', 'B', 'C', 'D']),
            'floor' => fake()->numberBetween(1, 9).'th Floor',
            'bill_month' => $billDate->format('F Y'),
            'bill_date' => $billDate->copy()->endOfMonth(),
            'due_date' => $billDate->copy()->addMonth()->day(15),
            'bill_cycle' => $billDate->format('F Y'),
            'billing_type' => 'Monthly Maintenance',
            'sub_total' => $subTotal,
            'discount' => 0,
            'late_fee' => 0,
            'tax_amount' => 0,
            'previous_dues' => 0,
            'total_amount' => $subTotal,
            'collected_amount' => $collected,
            'outstanding_amount' => $subTotal - $collected,
            'status' => $status,
            'send_email' => true,
            'send_sms' => true,
            'send_whatsapp' => false,
        ];
    }
}
