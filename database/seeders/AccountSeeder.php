<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Society;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Seed the 10 exact hierarchical rows shown on "chart of account.png" (page 1),
     * then fill each group with factory rows so the totals match the demo figures:
     * 78 accounts total, group counts Assets 28 / Liabilities 18 / Income 12 /
     * Expenses 12 / Equity 5 / Other Income 3, with 6 inactive (72 active).
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $groups = AccountGroup::pluck('id', 'name');

        // [code, name, group, type, tree_no, indent, balance]
        $named = [
            ['1000', 'Assets', 'Assets', 'group', '1', 0, 0],
            ['1100', 'Current Assets', 'Assets', 'group', '1.1', 1, 0],
            ['1110', 'Cash in Hand', 'Assets', 'detail', '1.1.1', 2, 147550.50],
            ['1120', 'Bank Accounts', 'Assets', 'group', '1.1.2', 2, 0],
            ['1121', 'SBI Bank A/c', 'Assets', 'detail', '1.1.2.1', 3, 825450],
            ['1122', 'HDFC Bank A/c', 'Assets', 'detail', '1.1.2.2', 3, 512320],
            ['1200', 'Fixed Assets', 'Assets', 'group', '1.2', 1, 679950],
            ['2000', 'Liabilities', 'Liabilities', 'group', '2', 0, 460150],
            ['2100', 'Current Liabilities', 'Liabilities', 'group', '2.1', 1, 310000],
            ['2110', 'Sundry Creditors', 'Liabilities', 'detail', '2.1.1', 2, 225600],
        ];

        $order = 1;
        foreach ($named as [$code, $name, $group, $type, $treeNo, $indent, $balance]) {
            Account::create([
                'society_id' => $societyId,
                'code' => $code,
                'name' => $name,
                'group_id' => $groups[$group],
                'type' => $type,
                'tree_no' => $treeNo,
                'indent' => $indent,
                'opening_balance' => $balance,
                'balance' => $balance,
                'status' => 'active',
                'display_order' => $order,
            ]);
            $order++;
        }

        // Remaining count per group so totals hit the demo figures. Named rows
        // already placed 7 accounts in Assets and 3 in Liabilities.
        $targets = [
            'Assets' => 28, 'Liabilities' => 18, 'Income' => 12,
            'Expenses' => 12, 'Equity' => 5, 'Other Income' => 3,
        ];
        $placed = ['Assets' => 7, 'Liabilities' => 3];

        $code = 3000;
        $displayOrder = 100;
        $inactiveBudget = 6;
        foreach ($targets as $group => $target) {
            $remaining = $target - ($placed[$group] ?? 0);
            for ($n = 0; $n < $remaining; $n++) {
                $factory = Account::factory();
                if ($inactiveBudget > 0) {
                    $factory = $factory->inactive();
                    $inactiveBudget--;
                }
                $factory->create([
                    'society_id' => $societyId,
                    'group_id' => $groups[$group],
                    'code' => (string) $code,
                    'display_order' => $displayOrder,
                ]);
                $code++;
                $displayOrder++;
            }
        }
    }
}
