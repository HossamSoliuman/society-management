<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->numberBetween(1000, 50000);
        $tax = fake()->randomElement([0, 0, round($amount * 0.05, 2)]);
        $paid = fake()->randomElement([$amount + $tax, 0]);
        $status = $paid >= ($amount + $tax) ? 'paid' : 'pending';

        return [
            'society_id' => null,
            'code' => 'EXP-'.date('Y').'-'.fake()->unique()->numerify('###'),
            'expense_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'title' => ucfirst(fake()->words(3, true)),
            'category_id' => ExpenseCategory::factory(),
            'vendor_id' => Vendor::factory(),
            'reference_no' => strtoupper(fake()->bothify('REF-####')),
            'payment_mode' => fake()->randomElement(['Cash', 'Card', 'UPI', 'Net Banking', 'Cheque', 'Bank Transfer']),
            'amount' => $amount,
            'tax_amount' => $tax,
            'paid_amount' => $paid,
            'due_amount' => ($amount + $tax) - $paid,
            'bill_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'payment_status' => $status,
            'expense_for' => 'Society',
            'description' => fake()->sentence(),
        ];
    }
}
