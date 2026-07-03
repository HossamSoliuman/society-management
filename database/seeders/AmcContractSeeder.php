<?php

namespace Database\Seeders;

use App\Models\AmcContract;
use App\Models\Society;
use Illuminate\Database\Seeder;

class AmcContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Society::first()) {
            return;
        }

        AmcContract::factory()->count(20)->create();
    }
}
