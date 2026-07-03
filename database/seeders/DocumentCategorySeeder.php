<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\Society;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Society::first()) {
            return;
        }

        DocumentCategory::factory()->count(10)->create();
    }
}
