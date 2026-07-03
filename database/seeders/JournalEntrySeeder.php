<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Society;
use Illuminate\Database\Seeder;

class JournalEntrySeeder extends Seeder
{
    /**
     * Seed 26 balanced journal entries (matching the "Total Journal Entries: 26"
     * stat), each with a debit line and a credit line that reconcile.
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $accountIds = Account::pluck('id')->all();

        // [entry_no, date, narration, amount]
        $named = [
            ['JV/2505/022', '2025-05-30', 'Depreciation on Fixed Assets for May 2025', 15000],
            ['JV/2505/021', '2025-05-28', 'Provision for outstanding maintenance', 22500],
            ['JV/2505/020', '2025-05-26', 'Interest income accrued on FD', 8750],
            ['JV/2505/019', '2025-05-24', 'Transfer to Reserve & Surplus fund', 50000],
            ['JV/2505/018', '2025-05-22', 'Prepaid insurance adjustment', 12000],
            ['JV/2505/017', '2025-05-20', 'Round-off and rectification entry', 350],
        ];

        $rows = $named;
        for ($i = 20; $i >= 1; $i--) {
            $rows[] = [
                'JV/2505/'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                fake()->dateTimeBetween('2025-04-01', '2025-05-18')->format('Y-m-d'),
                fake()->sentence(4),
                fake()->numberBetween(1000, 90000),
            ];
        }

        foreach ($rows as [$no, $date, $narration, $amount]) {
            $entry = JournalEntry::create([
                'society_id' => $societyId,
                'entry_no' => $no,
                'date' => $date,
                'narration' => $narration,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'status' => 'posted',
            ]);

            $entry->lines()->createMany([
                ['account_id' => collect($accountIds)->random(), 'debit' => $amount, 'credit' => 0],
                ['account_id' => collect($accountIds)->random(), 'debit' => 0, 'credit' => $amount],
            ]);
        }
    }
}
