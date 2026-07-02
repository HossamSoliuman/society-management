<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Society;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AssetCategorySeeder extends Seeder
{
    /**
     * Seed the 15 asset categories behind "Asset categories list.png" (10 on page 1,
     * 5 on page 2). Codes are looked up by AssetSeeder to attach assets.
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');

        // [name, code, icon, color, status, created_on]
        $rows = [
            ['Lift', 'LIFT', 'fa-elevator', 'blue', 'active', '2024-01-01'],
            ['Generator', 'GEN', 'fa-charging-station', 'gray', 'active', '2024-01-01'],
            ['Water Pump', 'WPMP', 'fa-faucet', 'blue', 'active', '2024-01-02'],
            ['Security', 'SEC', 'fa-shield-halved', 'red', 'active', '2024-01-02'],
            ['Fire Safety', 'FIRE', 'fa-fire-extinguisher', 'red', 'active', '2024-01-03'],
            ['Water Tank', 'WTNK', 'fa-droplet', 'blue', 'active', '2024-01-03'],
            ['Communication', 'COMM', 'fa-tower-broadcast', 'blue', 'active', '2024-01-04'],
            ['Garden Equipment', 'GARD', 'fa-leaf', 'green', 'active', '2024-01-04'],
            ['Parking Equipment', 'PARK', 'fa-car', 'orange', 'inactive', '2024-02-10'],
            ['Others', 'OTHR', 'fa-cube', 'gray', 'active', '2024-01-05'],
            ['Plumbing', 'PLMB', 'fa-faucet-drip', 'blue', 'active', '2024-02-15'],
            ['Electrical', 'ELEC', 'fa-bolt', 'yellow', 'active', '2024-02-18'],
            ['HVAC', 'HVAC', 'fa-fan', 'teal', 'active', '2024-02-20'],
            ['Furniture', 'FURN', 'fa-couch', 'orange', 'active', '2024-03-01'],
            ['IT Equipment', 'ITEQ', 'fa-computer', 'purple', 'active', '2024-03-05'],
        ];

        foreach ($rows as $order => [$name, $code, $icon, $color, $status, $createdOn]) {
            $category = AssetCategory::create([
                'society_id' => $societyId,
                'name' => $name,
                'code' => $code,
                'icon' => $icon,
                'color' => $color,
                'description' => $name.' assets and equipment.',
                'status' => $status,
                'display_order' => $order + 1,
                'movable' => true,
                'immovable' => true,
                'asset_life_years' => 10,
            ]);

            $timestamp = Carbon::parse($createdOn.' 09:00:00');
            $category->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->saveQuietly();
        }
    }
}
