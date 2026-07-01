<?php

namespace Database\Factories;

use App\Models\LateFeeSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LateFeeSetting>
 */
class LateFeeSettingFactory extends Factory
{
    protected $model = LateFeeSetting::class;

    public function definition(): array
    {
        return [
            'enable_late_fee' => true,
            'grace_period_days' => 15,
            'late_fee_type' => 'percentage',
            'late_fee_percent' => 2.00,
            'max_late_fee_cap' => 500.00,
            'enable_interest' => true,
            'interest_rate_annual' => 18.00,
            'apply_interest_after_days' => 30,
        ];
    }
}
