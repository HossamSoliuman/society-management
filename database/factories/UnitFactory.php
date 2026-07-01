<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $building = fake()->randomElement(['Building A', 'Building B', 'Building C', 'Building D']);
        $wing = 'Wing '.substr($building, -1);
        $floor = fake()->numberBetween(1, 12);
        $type = fake()->randomElement(['1 BHK', '2 BHK', '3 BHK']);
        $area = match ($type) {
            '1 BHK' => fake()->numberBetween(600, 700),
            '3 BHK' => fake()->numberBetween(1150, 1300),
            default => fake()->numberBetween(900, 1000),
        };

        $ownerName = fake()->name();
        $ownerMobile = '98765'.fake()->numerify('#####');

        return [
            'unit_number' => substr($building, -1).'-'.$floor.fake()->numerify('0#'),
            'building' => $building,
            'wing' => $wing,
            'floor' => $this->ordinal($floor).' Floor',
            'unit_type' => $type,
            'area_sqft' => $area,
            'status' => 'vacant',
            'occupied_by_name' => null,
            'occupied_by_role' => null,
            'owner_name' => $ownerName,
            'owner_mobile' => $ownerMobile,
        ];
    }

    public function occupied(): static
    {
        return $this->state(function (array $attributes) {
            $role = fake()->randomElement(['Owner', 'Tenant']);

            return [
                'status' => 'occupied',
                'occupied_by_name' => $role === 'Owner' ? $attributes['owner_name'] : fake()->name(),
                'occupied_by_role' => $role,
            ];
        });
    }

    public function vacant(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vacant',
            'occupied_by_name' => null,
            'occupied_by_role' => null,
        ]);
    }

    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_maintenance',
            'occupied_by_name' => null,
            'occupied_by_role' => null,
        ]);
    }

    private function ordinal(int $number): string
    {
        $suffix = ['th', 'st', 'nd', 'rd'];
        $mod = $number % 100;

        return $number.($suffix[($mod - 20) % 10] ?? $suffix[$mod] ?? $suffix[0]);
    }
}
