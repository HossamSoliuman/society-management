<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'society_id' => null,
            'name' => fake()->company(),
            'company' => fake()->companySuffix(),
            'phone' => fake()->numerify('98########'),
            'email' => fake()->safeEmail(),
            'gst_number' => strtoupper(fake()->bothify('##?????####?#Z#')),
            'address' => fake()->address(),
            'category_id' => null,
            'status' => 'active',
            'notes' => null,
        ];
    }
}
