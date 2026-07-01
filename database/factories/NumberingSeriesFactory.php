<?php

namespace Database\Factories;

use App\Models\NumberingSeries;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NumberingSeries>
 */
class NumberingSeriesFactory extends Factory
{
    protected $model = NumberingSeries::class;

    public function definition(): array
    {
        return [
            'document_type' => 'maintenance_bill',
            'is_default' => false,
            'prefix' => 'MB-',
            'format' => 'YYYY-#####',
            'next_number' => fake()->numberBetween(1, 1000),
            'reset_frequency' => 'yearly',
            'financial_year' => '2025-2026',
            'start_date' => '2025-04-01',
            'description' => fake()->sentence(4),
            'status' => 'active',
        ];
    }
}
