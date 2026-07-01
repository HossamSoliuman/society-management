<?php

namespace Database\Factories;

use App\Models\CollectionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CollectionPayment>
 */
class CollectionPaymentFactory extends Factory
{
    protected $model = CollectionPayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $receiptDate = Carbon::parse(fake()->dateTimeBetween('-3 months', 'now'));
        $totalDue = fake()->randomElement([2850, 3150, 3300]);
        $status = fake()->randomElement(['paid', 'partial', 'pending']);

        $paid = match ($status) {
            'paid' => $totalDue,
            'partial' => (int) round($totalDue * fake()->randomFloat(2, 0.2, 0.6)),
            default => 0,
        };

        $mode = $paid > 0 ? fake()->randomElement(['cash', 'upi', 'card', 'net_banking', 'cheque']) : null;

        return [
            'receipt_number' => 'RCPT-'.$receiptDate->format('Y').'-'.fake()->unique()->numberBetween(1000, 9999),
            'member_name' => fake()->name(),
            'member_mobile' => fake()->numerify('98########'),
            'member_email' => fake()->safeEmail(),
            'flat_number' => strtoupper(fake()->randomLetter()).'-'.fake()->numberBetween(101, 904),
            'unit_label' => 'Building '.fake()->randomElement(['A', 'B', 'C']).', Wing '.fake()->randomElement(['A', 'B']),
            'unit_type' => fake()->randomElement(['1 BHK', '2 BHK', '3 BHK']),
            'bill_type' => 'Maintenance',
            'bill_period' => $receiptDate->format('F Y'),
            'due_date' => $receiptDate->copy()->endOfMonth(),
            'receipt_date' => $receiptDate,
            'total_due' => $totalDue,
            'paid_amount' => $paid,
            'discount' => 0,
            'fine_penalty' => 0,
            'balance_due' => $totalDue - $paid,
            'payment_mode' => $mode,
            'collected_by' => $paid > 0 ? fake()->randomElement(['Neha Patil', 'Sanjay Verma']) : null,
            'status' => $status,
            'is_online' => false,
        ];
    }
}
