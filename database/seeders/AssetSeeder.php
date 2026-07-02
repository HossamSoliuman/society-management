<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Society;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Seed the 8 exact rows shown on "Assets Management.png" (AST0001–AST0008), then
     * fill each category with factory rows so the totals match the demo figures:
     * 128 assets, and the right-rail per-category counts (Lift 4 … Others 71).
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $categories = AssetCategory::pluck('id', 'code');

        // [name, code, category-code, brand, location, purchase_date, cost, status, condition]
        $named = [
            ['Passenger Lift - A', 'AST0001', 'LIFT', 'Otis Gen2', 'Tower A - Lobby', '2021-03-15', 850000, 'in_use', 'good'],
            ['Diesel Generator 125 kVA', 'AST0002', 'GEN', 'Kirloskar', 'Basement - Genset Room', '2020-07-10', 620000, 'in_use', 'good'],
            ['Booster Water Pump', 'AST0003', 'WPMP', 'Grundfos', 'Basement - Pump Room', '2022-01-20', 145000, 'under_maintenance', 'fair'],
            ['CCTV Camera - Main Gate', 'AST0004', 'SEC', 'Hikvision', 'Main Entrance', '2023-05-05', 18500, 'in_use', 'good'],
            ['Fire Extinguisher - Lobby', 'AST0005', 'FIRE', 'Ceasefire', 'Tower B - Lobby', '2023-02-11', 4500, 'in_use', 'good'],
            ['Overhead Water Tank', 'AST0006', 'WTNK', 'Sintex', 'Terrace - Tower A', '2019-11-30', 95000, 'in_use', 'good'],
            ['Intercom System', 'AST0007', 'COMM', 'Panasonic', 'Security Cabin', '2020-09-18', 62000, 'inactive', 'poor'],
            ['Lawn Mower', 'AST0008', 'GARD', 'Honda', 'Garden - Central', '2022-06-25', 38000, 'in_use', 'good'],
        ];

        foreach ($named as $i => [$name, $assetCode, $catCode, $brand, $location, $purchaseDate, $cost, $status, $condition]) {
            Asset::create([
                'society_id' => $societyId,
                'name' => $name,
                'asset_code' => $assetCode,
                'category_id' => $categories[$catCode],
                'brand' => $brand,
                'location' => $location,
                'purchase_date' => $purchaseDate,
                'purchase_cost' => $cost,
                'current_value' => round($cost * 0.8),
                'status' => $status,
                'condition' => $condition,
            ]);
        }

        // Remaining count per category so the totals hit the demo figures.
        // Named rows already placed one asset in each of the first 8 categories.
        $targets = [
            'LIFT' => 4, 'GEN' => 3, 'WPMP' => 6, 'SEC' => 15, 'FIRE' => 10,
            'WTNK' => 8, 'COMM' => 6, 'GARD' => 5, 'OTHR' => 71,
        ];
        $placed = ['LIFT' => 1, 'GEN' => 1, 'WPMP' => 1, 'SEC' => 1, 'FIRE' => 1, 'WTNK' => 1, 'COMM' => 1, 'GARD' => 1, 'OTHR' => 0];

        $seq = 9;
        foreach ($targets as $code => $target) {
            $remaining = $target - ($placed[$code] ?? 0);
            for ($n = 0; $n < $remaining; $n++) {
                Asset::factory()->create([
                    'society_id' => $societyId,
                    'category_id' => $categories[$code],
                    'asset_code' => 'AST'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                ]);
                $seq++;
            }
        }
    }
}
