<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Society;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();

        if (! $society) {
            return;
        }

        $members = [
            ['name' => 'Ramesh Sharma', 'member_type' => 'owner', 'flat_unit' => 'A-101', 'tower_wing' => 'Tower A', 'mobile' => '9876543210', 'email' => 'ramesh.sharma@email.com', 'status' => 'active', 'join_date' => '2024-01-15'],
            ['name' => 'Priya Sharma', 'member_type' => 'family_member', 'flat_unit' => 'A-101', 'tower_wing' => 'Tower A', 'mobile' => '9876543211', 'email' => 'priya.sharma@email.com', 'status' => 'active', 'join_date' => '2024-01-15'],
            ['name' => 'Amit Verma', 'member_type' => 'owner', 'flat_unit' => 'B-203', 'tower_wing' => 'Tower B', 'mobile' => '9876543212', 'email' => 'amit.verma@email.com', 'status' => 'active', 'join_date' => '2024-02-10'],
            ['name' => 'Neha Verma', 'member_type' => 'family_member', 'flat_unit' => 'B-203', 'tower_wing' => 'Tower B', 'mobile' => '9876543213', 'email' => 'neha.verma@email.com', 'status' => 'active', 'join_date' => '2024-02-10'],
            ['name' => 'Sanjay Patel', 'member_type' => 'tenant', 'flat_unit' => 'C-302', 'tower_wing' => 'Tower C', 'mobile' => '9876543214', 'email' => 'sanjay.patel@email.com', 'status' => 'inactive', 'join_date' => '2024-03-05'],
            ['name' => 'Meena Patel', 'member_type' => 'tenant', 'flat_unit' => 'C-302', 'tower_wing' => 'Tower C', 'mobile' => '9876543215', 'email' => 'meena.patel@email.com', 'status' => 'inactive', 'join_date' => '2024-03-05'],
            ['name' => 'Vikram Singh', 'member_type' => 'owner', 'flat_unit' => 'D-404', 'tower_wing' => 'Tower D', 'mobile' => '9876543216', 'email' => 'vikram.singh@email.com', 'status' => 'blocked', 'join_date' => '2024-04-12'],
            ['name' => 'Anjali Singh', 'member_type' => 'family_member', 'flat_unit' => 'D-404', 'tower_wing' => 'Tower D', 'mobile' => '9876543217', 'email' => 'anjali.singh@email.com', 'status' => 'active', 'join_date' => '2024-04-12'],
        ];

        foreach ($members as $member) {
            $society->members()->create($member);
        }

        // Seeded named rows: 5 active, 2 inactive, 1 blocked.
        // Target totals: 236 active / 15 inactive / 5 blocked = 256 total.
        Member::factory()->count(231)->active()->create(['society_id' => $society->id]);
        Member::factory()->count(13)->inactive()->create(['society_id' => $society->id]);
        Member::factory()->count(4)->blocked()->create(['society_id' => $society->id]);
    }
}
