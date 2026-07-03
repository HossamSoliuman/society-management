<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Society;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Society::first()) {
            return;
        }

        Document::factory()->count(25)->create();
    }
}
