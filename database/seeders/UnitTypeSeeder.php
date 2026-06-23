<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitType;

class UnitTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Flat / Apartment', 'description' => 'Independent flats or apartments'],
            ['name' => 'Shop / Retail', 'description' => 'Shops and retail spaces'],
            ['name' => 'Office', 'description' => 'Office spaces'],
            ['name' => 'Warehouse', 'description' => 'Warehouse or storage units'],
            ['name' => 'Penthouse', 'description' => 'Penthouse units'],
            ['name' => 'Studio Apartment', 'description' => 'Studio or 1 RK units'],
            ['name' => 'Plot', 'description' => 'Residential or commercial plots'],
        ];

        foreach ($types as $type) {
            UnitType::create($type);
        }
    }
}
