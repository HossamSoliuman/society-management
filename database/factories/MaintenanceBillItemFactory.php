<?php

namespace Database\Factories;

use App\Models\MaintenanceBill;
use App\Models\MaintenanceBillItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceBillItem>
 */
class MaintenanceBillItemFactory extends Factory
{
    protected $model = MaintenanceBillItem::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'Maintenance Charges', 'Sinking Fund', 'Reserve Fund', 'Water Charges', 'Others',
        ]);

        return [
            'maintenance_bill_id' => MaintenanceBill::factory(),
            'charge_head_id' => null,
            'charge_head_name' => $name,
            'description' => fake()->sentence(3),
            'amount' => fake()->randomElement([200, 300, 500, 2500]),
            'sort_order' => 0,
        ];
    }
}
