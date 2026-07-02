<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = fake()->numberBetween(5000, 500000);

        return [
            'society_id' => null,
            'name' => ucfirst(fake()->words(2, true)),
            'asset_code' => 'AST'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'category_id' => AssetCategory::factory(),
            'brand' => fake()->company(),
            'model' => Str::upper(fake()->bothify('??-###')),
            'serial_number' => fake()->bothify('SN########'),
            'description' => fake()->sentence(),
            'location' => fake()->randomElement(['Tower A', 'Tower B', 'Basement', 'Terrace', 'Clubhouse']),
            'tower_wing' => fake()->randomElement(['A', 'B', 'C']),
            'floor' => fake()->randomElement(['Ground', '1st', '2nd', 'Basement']),
            'area_room' => fake()->randomElement(['Lobby', 'Pump Room', 'Gym', 'Garden']),
            'assigned_to' => fake()->randomElement(['Maintenance Team', 'Security', 'Housekeeping']),
            'vendor_supplier' => fake()->company(),
            'purchase_from' => fake()->company(),
            'purchase_date' => fake()->dateTimeBetween('-4 years', '-1 month'),
            'purchase_cost' => $cost,
            'warranty_start' => null,
            'warranty_end' => null,
            'invoice_no' => fake()->bothify('INV-####'),
            'invoice_date' => null,
            'expected_life_years' => fake()->numberBetween(5, 20),
            'depreciation_method' => fake()->randomElement(['Straight Line', 'Written Down Value']),
            'status' => fake()->randomElement(['in_use', 'under_maintenance', 'inactive']),
            'condition' => fake()->randomElement(['good', 'fair', 'poor']),
            'usage_type' => fake()->randomElement(['Common', 'Dedicated']),
            'qr_code' => null,
            'notes' => null,
            'current_value' => $cost * 0.8,
        ];
    }
}
