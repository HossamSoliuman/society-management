<?php

namespace Database\Factories;

use App\Models\AccountingPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountingPayment>
 */
class AccountingPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_no' => 'PMT/25-26/'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payee' => $this->faker->company(),
            'purpose' => $this->faker->randomElement(['Housekeeping', 'Electricity Bill', 'Water Charges', 'Security Services', 'Repairs']),
            'mode' => $this->faker->randomElement(['UPI', 'Card', 'Net Banking', 'Cheque', 'Cash']),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'account_id' => null,
            'reference_no' => strtoupper($this->faker->bothify('PAY######')),
            'status' => 'completed',
        ];
    }
}
