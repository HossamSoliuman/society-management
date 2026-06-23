<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocietyType;

class SocietyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Residential', 'description' => 'Residential apartment societies'],
            ['name' => 'Commercial', 'description' => 'Commercial complexes and buildings'],
            ['name' => 'Mixed Use', 'description' => 'Residential and commercial mixed societies'],
            ['name' => 'Industrial', 'description' => 'Industrial and warehouse societies'],
            ['name' => 'Villa / Bungalow', 'description' => 'Individual villa or bungalow societies'],
            ['name' => 'Co-operative Housing', 'description' => 'Co-operative housing societies'],
            ['name' => 'Others', 'description' => 'Other types of societies'],
        ];

        foreach ($types as $type) {
            SocietyType::create($type);
        }
    }
}
