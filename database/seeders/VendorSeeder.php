<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Society;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $categories = ExpenseCategory::query()
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->get()
            ->keyBy('name');

        // [name, company, phone, email, gst, category, status]
        $rows = [
            ['MSEDCL', 'Maharashtra State Electricity', '9820011223', 'billing@msedcl.in', '27AAECM1234A1Z5', 'Utilities', 'active'],
            ['Shri Sai Services', 'Shri Sai Facility Pvt. Ltd.', '9820022334', 'accounts@shrisai.in', '27AABCS4567B1Z2', 'Salary', 'active'],
            ['Otis Elevator Co.', 'Otis Elevator Company', '9820033445', 'support@otis.com', '27AAACO7654C1Z9', 'Lift Maintenance', 'active'],
            ['Green Earth Pvt. Ltd.', 'Green Earth Landscapes', '9820044556', 'hello@greenearth.in', '27AAECG9876D1Z3', 'Garden Maintenance', 'active'],
            ['Rentokil Initial', 'Rentokil PCI', '9820055667', 'care@rentokil.in', '27AAFCR2345E1Z7', 'Pest Control', 'active'],
            ['Om Plumbing', 'Om Plumbing Works', '9820066778', 'om.plumbing@email.com', '27AAHPO3456F1Z1', 'Repairs', 'active'],
            ['Nirman Hardware', 'Nirman Hardware Store', '9820077889', 'sales@nirmanhardware.in', '27AAJPN4567G1Z4', 'Purchase', 'active'],
            ['Safe Guard Pvt. Ltd.', 'Safe Guard Security Services', '9820088990', 'ops@safeguard.in', '27AAKCS5678H1Z8', 'Security', 'active'],
            ['Sparkle Cleaners', 'Sparkle Housekeeping', '9820099001', 'info@sparkle.in', '27AALCS6789I1Z6', 'Cleaning', 'active'],
            ['City Office Supplies', 'City Stationers', '9820100112', 'orders@cityoffice.in', '27AAMCC7890J1Z2', 'Admin Expenses', 'inactive'],
        ];

        foreach ($rows as [$name, $company, $phone, $email, $gst, $categoryName, $status]) {
            Vendor::create([
                'society_id' => $societyId,
                'name' => $name,
                'company' => $company,
                'phone' => $phone,
                'email' => $email,
                'gst_number' => $gst,
                'address' => 'Mumbai, Maharashtra',
                'category_id' => $categories->get($categoryName)?->id,
                'status' => $status,
            ]);
        }
    }
}
