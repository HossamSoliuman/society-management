<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['CGST (Central GST)', 'SGST (State GST)', 'IGST']),
            'tax_type' => 'percentage',
            'rate' => 9.00,
            'apply_on' => 'All Charge Heads',
            'slab_from' => null,
            'slab_to' => null,
            'description' => fake()->sentence(4),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
