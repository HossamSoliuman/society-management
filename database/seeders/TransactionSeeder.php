<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Society;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Seed the recent transactions shown on "accounting.png" / "accounting transactions.png"
     * (the 10 newest rows), then fill with factory rows so the total is 162.
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $accounts = Account::pluck('id', 'code');

        // [date, type, reference_no, description, account-code, mode, debit, credit, running_balance]
        $named = [
            ['2025-05-30', 'journal', 'JV/2505/022', 'Depreciation - Fixed Assets', '1200', null, 15000, 0, 1485320.50],
            ['2025-05-29', 'payment', 'PMT/2505/031', 'Electricity Bill - May', '1122', 'Net Banking', 42000, 0, 1470320.50],
            ['2025-05-28', 'receipt', 'RCPT/2505/018', 'Maintenance - A-101', '1121', 'UPI', 0, 8500, 1512320.50],
            ['2025-05-27', 'receipt', 'RCPT/2505/015', 'Amenities - Clubhouse Hall', '1121', 'Card', 0, 3500, 1503820.50],
            ['2025-05-26', 'receipt', 'RCPT/2505/012', 'Maintenance - B-204', '1121', 'UPI', 0, 8500, 1500320.50],
            ['2025-05-25', 'payment', 'PMT/2505/028', 'Housekeeping Charges', '1110', 'Cash', 18000, 0, 1491820.50],
            ['2025-05-24', 'receipt', 'RCPT/2505/010', 'Other Charges - C-301', '1122', 'Net Banking', 0, 2500, 1509820.50],
            ['2025-05-23', 'journal', 'JV/2505/019', 'Interest Income Accrual', '1110', null, 0, 5000, 1507320.50],
            ['2025-05-22', 'payment', 'PMT/2505/024', 'Water Tanker Supply', '1110', 'Cash', 6000, 0, 1502320.50],
            ['2025-05-21', 'receipt', 'RCPT/2505/008', 'Maintenance - A-405', '1121', 'UPI', 0, 8500, 1508320.50],
        ];

        foreach ($named as [$date, $type, $ref, $desc, $accCode, $mode, $debit, $credit, $running]) {
            Transaction::create([
                'society_id' => $societyId,
                'date' => $date,
                'type' => $type,
                'reference_no' => $ref,
                'description' => $desc,
                'account_id' => $accounts[$accCode] ?? null,
                'payment_mode' => $mode,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $running,
                'location' => 'Tower A',
            ]);
        }

        Transaction::factory()
            ->count(152)
            ->state(fn () => [
                'account_id' => $accounts->random(),
                'date' => fake()->dateTimeBetween('2025-04-01', '2025-05-20'),
            ])
            ->create(['society_id' => $societyId]);
    }
}
