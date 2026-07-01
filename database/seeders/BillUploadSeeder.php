<?php

namespace Database\Seeders;

use App\Models\BillUpload;
use App\Models\Society;
use Illuminate\Database\Seeder;

class BillUploadSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();

        if (! $society) {
            return;
        }

        BillUpload::updateOrCreate(
            ['society_id' => $society->id, 'original_name' => 'maintenance_bills_june2025.xlsx'],
            [
                'stored_path' => null,
                'uploaded_by' => 'Super Admin',
                'records_count' => 156,
                'status' => 'validated',
                'created_at' => '2025-05-30 11:30:00',
                'updated_at' => '2025-05-30 11:30:00',
            ]
        );
    }
}
