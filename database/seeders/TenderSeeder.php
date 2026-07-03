<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\Tender;
use Illuminate\Database\Seeder;

class TenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Society::first()) {
            return;
        }

        Tender::factory()->count(12)->create();
        Tender::factory()->count(5)->draft()->create();
        Tender::factory()->count(8)->awarded()->create();
        Tender::factory()->count(15)->closed()->create();
    }
}
