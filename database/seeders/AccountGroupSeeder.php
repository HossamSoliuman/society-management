<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use App\Models\Society;
use Illuminate\Database\Seeder;

class AccountGroupSeeder extends Seeder
{
    /**
     * The 6 account groups shown in the Chart of Accounts right rail.
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');

        // [name, color, icon]
        $groups = [
            ['Assets', 'green', 'fa-building'],
            ['Liabilities', 'blue', 'fa-hand-holding-dollar'],
            ['Income', 'purple', 'fa-arrow-down'],
            ['Expenses', 'orange', 'fa-arrow-up'],
            ['Equity', 'blue', 'fa-scale-balanced'],
            ['Other Income', 'pink', 'fa-coins'],
        ];

        foreach ($groups as [$name, $color, $icon]) {
            AccountGroup::create([
                'society_id' => $societyId,
                'name' => $name,
                'color' => $color,
                'icon' => $icon,
            ]);
        }
    }
}
