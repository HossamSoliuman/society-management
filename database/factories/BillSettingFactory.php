<?php

namespace Database\Factories;

use App\Models\BillSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillSetting>
 */
class BillSettingFactory extends Factory
{
    protected $model = BillSetting::class;

    public function definition(): array
    {
        return [
            'society_name' => fake()->company(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
        ];
    }
}
