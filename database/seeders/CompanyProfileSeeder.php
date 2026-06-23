<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyProfile;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::create([
            'company_name' => 'Greenfield Residency',
            'registration_number' => 'GRS/2020/1234',
            'email' => 'info@greenfieldresidency.com',
            'phone' => '+91 98765 43210',
            'website' => 'www.greenfieldresidency.com',
            'address' => 'Greenfield Residency, Near City Center, Vastrapur, Ahmedabad, Gujarat - 380015',
            'gst_number' => '24ABCDE1234F1Z5',
            'pan_number' => 'ABCDE1234F',
        ]);
    }
}
