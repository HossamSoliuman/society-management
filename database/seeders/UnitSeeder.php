<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();

        if (! $society) {
            return;
        }

        $units = [
            ['unit_number' => 'A-101', 'building' => 'Building A', 'wing' => 'Wing A', 'floor' => '1st Floor', 'unit_type' => '2 BHK', 'area_sqft' => 950, 'status' => 'occupied', 'occupied_by_name' => 'Rahul Sharma', 'occupied_by_role' => 'Owner', 'owner_name' => 'Rahul Sharma', 'owner_mobile' => '9876543210'],
            ['unit_number' => 'A-102', 'building' => 'Building A', 'wing' => 'Wing A', 'floor' => '1st Floor', 'unit_type' => '1 BHK', 'area_sqft' => 650, 'status' => 'vacant', 'occupied_by_name' => null, 'occupied_by_role' => null, 'owner_name' => 'Priya Sharma', 'owner_mobile' => '9876543211'],
            ['unit_number' => 'A-103', 'building' => 'Building A', 'wing' => 'Wing A', 'floor' => '1st Floor', 'unit_type' => '2 BHK', 'area_sqft' => 950, 'status' => 'occupied', 'occupied_by_name' => 'Neha Patel', 'occupied_by_role' => 'Tenant', 'owner_name' => 'Amit Patel', 'owner_mobile' => '9876543212'],
            ['unit_number' => 'A-201', 'building' => 'Building A', 'wing' => 'Wing A', 'floor' => '2nd Floor', 'unit_type' => '2 BHK', 'area_sqft' => 950, 'status' => 'under_maintenance', 'occupied_by_name' => null, 'occupied_by_role' => null, 'owner_name' => 'Sanjay Patel', 'owner_mobile' => '9876543213'],
            ['unit_number' => 'A-202', 'building' => 'Building A', 'wing' => 'Wing A', 'floor' => '2nd Floor', 'unit_type' => '3 BHK', 'area_sqft' => 1250, 'status' => 'occupied', 'occupied_by_name' => 'Amit Kumar', 'occupied_by_role' => 'Tenant', 'owner_name' => 'Amit Kumar', 'owner_mobile' => '9876543214'],
            ['unit_number' => 'B-101', 'building' => 'Building B', 'wing' => 'Wing B', 'floor' => '1st Floor', 'unit_type' => '2 BHK', 'area_sqft' => 980, 'status' => 'occupied', 'occupied_by_name' => 'Sneha Iyer', 'occupied_by_role' => 'Owner', 'owner_name' => 'Sneha Iyer', 'owner_mobile' => '9876543215'],
            ['unit_number' => 'B-102', 'building' => 'Building B', 'wing' => 'Wing B', 'floor' => '1st Floor', 'unit_type' => '2 BHK', 'area_sqft' => 980, 'status' => 'vacant', 'occupied_by_name' => null, 'occupied_by_role' => null, 'owner_name' => 'Vikram Singh', 'owner_mobile' => '9876543216'],
            ['unit_number' => 'B-103', 'building' => 'Building B', 'wing' => 'Wing B', 'floor' => '1st Floor', 'unit_type' => '1 BHK', 'area_sqft' => 620, 'status' => 'occupied', 'occupied_by_name' => 'Vikram Singh', 'occupied_by_role' => 'Tenant', 'owner_name' => 'Vikram Singh', 'owner_mobile' => '9876543216'],
        ];

        foreach ($units as $unit) {
            $society->units()->create($unit);
        }

        // Seeded named rows: 5 occupied, 2 vacant, 1 under maintenance.
        // Target totals: 192 occupied / 48 vacant / 16 under maintenance = 256 total.
        Unit::factory()->count(187)->occupied()->create(['society_id' => $society->id]);
        Unit::factory()->count(46)->vacant()->create(['society_id' => $society->id]);
        Unit::factory()->count(15)->underMaintenance()->create(['society_id' => $society->id]);
    }
}
