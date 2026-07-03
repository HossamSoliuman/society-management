<?php

namespace Database\Factories;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receipt_no' => 'RCPT/25-26/'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payer_name' => $this->faker->name(),
            'flat_no' => $this->faker->randomElement(['A', 'B', 'C']).'-'.$this->faker->numberBetween(101, 904),
            'receipt_type' => $this->faker->randomElement(['maintenance', 'other_charges', 'amenities', 'interest_penalty']),
            'reference_no' => strtoupper($this->faker->bothify('TXN######')),
            'mode_of_payment' => $this->faker->randomElement(['UPI', 'Card', 'Net Banking', 'Cash']),
            'amount' => $this->faker->randomFloat(2, 500, 15000),
            'account_id' => null,
            'location' => $this->faker->randomElement(['Tower A', 'Tower B', 'Clubhouse']),
            'status' => 'completed',
        ];
    }
}
