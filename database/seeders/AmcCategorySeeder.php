<?php

namespace Database\Seeders;

use App\Models\AmcCategory;
use App\Models\Society;
use Illuminate\Database\Seeder;

class AmcCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Society::first()) {
            return;
        }

        AmcCategory::factory()->count(10)->create();
    }
}
