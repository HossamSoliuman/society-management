<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetImage>
 */
class AssetImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'path' => 'asset-images/'.fake()->uuid().'.jpg',
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
