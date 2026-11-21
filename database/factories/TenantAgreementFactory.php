<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantAgreement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantAgreement>
 */
class TenantAgreementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->subMonths(fake()->numberBetween(1, 10))->startOfMonth();

        return [
            'tenant_id' => Tenant::factory(),
            'society_id' => fn (array $attributes) => Tenant::find($attributes['tenant_id'])?->society_id,
            'start_date' => $start,
            'end_date' => $start->copy()->addMonths(11)->subDay(),
            'monthly_rent' => fake()->randomElement([18000, 22000, 25000, 32000]),
            'security_deposit' => 50000,
            'status' => 'active',
        ];
    }
}
