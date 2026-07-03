<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['receipt', 'payment', 'journal']);
        $amount = $this->faker->randomFloat(2, 500, 75000);

        return [
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'type' => $type,
            'reference_no' => strtoupper($this->faker->bothify('REF/####/##')),
            'description' => $this->faker->sentence(3),
            'account_id' => null,
            'payment_mode' => $this->faker->randomElement(['UPI', 'Card', 'Net Banking', 'Cash', 'Cheque']),
            'debit' => $type === 'payment' ? $amount : 0,
            'credit' => $type === 'receipt' ? $amount : 0,
            'running_balance' => $this->faker->randomFloat(2, 100000, 1500000),
            'location' => $this->faker->randomElement(['Tower A', 'Tower B', 'Clubhouse']),
        ];
    }
}
