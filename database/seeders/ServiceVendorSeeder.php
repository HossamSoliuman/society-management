<?php

namespace Database\Seeders;

use App\Models\ServiceVendor;
use App\Models\Society;
use Illuminate\Database\Seeder;

class ServiceVendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Society::first()) {
            return;
        }

        ServiceVendor::factory()->count(12)->create();
    }
}
