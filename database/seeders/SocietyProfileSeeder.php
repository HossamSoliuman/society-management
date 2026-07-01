<?php

namespace Database\Seeders;

use App\Models\Society;
use Illuminate\Database\Seeder;

class SocietyProfileSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();

        if (! $society) {
            return;
        }

        $society->update([
            'name' => 'Green Meadows Residency',
            'society_code' => 'GMR/2024/001',
            'registration_number' => 'MH/RAIGAD/PANVEL/2024/12345',
            'rera_number' => 'P52000012345',
            'building_type' => 'Apartment',
            'year_established' => 2018,
            'wings_count' => 3,
            'blocks_count' => 6,
            'total_units' => 240,
            'flats_count' => 240,
            'shops_count' => 0,
            'offices_count' => 0,
            'address_line_1' => 'Green Meadows Residency, Sector 15',
            'address_line_2' => 'New Panvel',
            'city' => 'Navi Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '410206',
            'primary_mobile' => '+91 98765 43210',
            'primary_email' => 'info@greenmeadows.in',
            'website' => 'www.greenmeadows.in',
            'office_timings' => '9:00 AM - 6:00 PM (Mon - Sat)',
            'management_type' => 'Society Managed',
            'committee_members_count' => 11,
            'audit_type' => 'Internal Audit',
            'financial_year' => 'April - March',
            'maintenance_collection_day' => '1st to 10th of Every Month',
            'bank_name' => 'State Bank of India',
            'account_number' => 'xxxxxxxx1234',
            'ifsc_code' => 'SBIN0001234',
            'pan_number' => 'ABCDE1234F',
            'gst_number' => '27AABCDE1234F1Z5',
            'amenities' => [
                'Club House', 'Gymnasium', 'Children Play Area', 'Swimming Pool',
                'Garden', '24x7 Security', 'Power Backup', 'Lift / Elevators',
            ],
            'about' => "Green Meadows Residency is a premium residential community located in the heart of New Panvel. Our mission is to provide a safe, clean, and comfortable living environment for all residents.\n\nWe are committed to maintaining high standards of transparency, cleanliness, and timely services.",
            'status' => 'active',
        ]);

        $documents = [
            'Registration Certificate',
            'Bylaws',
            'Building Plan Approval',
            'Fire NOC',
        ];

        $society->documents()->delete();

        foreach ($documents as $title) {
            $society->documents()->create([
                'title' => $title,
                'file_path' => null,
                'uploaded_at' => '2024-05-12',
            ]);
        }
    }
}
