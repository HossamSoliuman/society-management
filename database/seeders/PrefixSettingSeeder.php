<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrefixSetting;

class PrefixSettingSeeder extends Seeder
{
    public function run(): void
    {
        $prefixes = [
            ['module' => 'Society', 'prefix' => 'SOC', 'starting_number' => 1, 'current_number' => 128, 'padding' => 4],
            ['module' => 'Invoice', 'prefix' => 'INV', 'starting_number' => 1, 'current_number' => 2056, 'padding' => 4],
            ['module' => 'Receipt', 'prefix' => 'RCPT', 'starting_number' => 1, 'current_number' => 1072, 'padding' => 4],
            ['module' => 'Refund', 'prefix' => 'RFND', 'starting_number' => 1, 'current_number' => 18, 'padding' => 4],
            ['module' => 'Ticket', 'prefix' => 'TKT', 'starting_number' => 1, 'current_number' => 125, 'padding' => 4],
            ['module' => 'Member', 'prefix' => 'MBR', 'starting_number' => 1, 'current_number' => 5642, 'padding' => 4],
        ];

        foreach ($prefixes as $prefix) {
            PrefixSetting::create($prefix);
        }
    }
}
