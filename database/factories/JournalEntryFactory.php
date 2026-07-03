<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 1000, 90000);

        return [
            'entry_no' => 'JV/2505/'.str_pad((string) $this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'narration' => $this->faker->sentence(4),
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
        ];
    }
}
