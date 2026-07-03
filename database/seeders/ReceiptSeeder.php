<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Receipt;
use App\Models\Society;
use Illuminate\Database\Seeder;

class ReceiptSeeder extends Seeder
{
    /**
     * Seed the 8 exact rows shown on "accounting receipts.png" (page 1), then fill
     * with factory rows so the total is 248 (footer "Showing 1 to 8 of 248 receipts").
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $accounts = Account::pluck('id', 'code');
        $sbi = $accounts['1121'] ?? null;
        $hdfc = $accounts['1122'] ?? null;
        $cash = $accounts['1110'] ?? null;

        // [receipt_no, date, payer, flat, type, ref, mode, amount, account]
        $named = [
            ['RCPT/25-26/0248', '2025-05-30 14:35:00', 'Rajesh Kumar', 'A-101', 'maintenance', 'TXN845210', 'UPI', 8500, $sbi],
            ['RCPT/25-26/0247', '2025-05-30 11:20:00', 'Priya Sharma', 'B-204', 'maintenance', 'TXN845198', 'Net Banking', 8500, $hdfc],
            ['RCPT/25-26/0246', '2025-05-29 16:10:00', 'Amit Patel', 'C-301', 'other_charges', 'TXN845155', 'Card', 2500, $sbi],
            ['RCPT/25-26/0245', '2025-05-29 10:05:00', 'Sneha Reddy', 'A-402', 'amenities', 'TXN845102', 'UPI', 3500, $sbi],
            ['RCPT/25-26/0244', '2025-05-28 15:45:00', 'Vikram Singh', 'B-105', 'maintenance', 'TXN845067', 'Cash', 8500, $cash],
            ['RCPT/25-26/0243', '2025-05-28 09:30:00', 'Anjali Mehta', 'C-203', 'interest_penalty', 'TXN845021', 'Net Banking', 500, $hdfc],
            ['RCPT/25-26/0242', '2025-05-27 17:25:00', 'Rohan Gupta', 'A-304', 'maintenance', 'TXN844988', 'UPI', 8500, $sbi],
            ['RCPT/25-26/0241', '2025-05-27 12:15:00', 'Kavya Nair', 'B-401', 'other_charges', 'TXN844943', 'Card', 2500, $hdfc],
        ];

        foreach ($named as [$no, $date, $payer, $flat, $type, $ref, $mode, $amount, $account]) {
            Receipt::create([
                'society_id' => $societyId,
                'receipt_no' => $no,
                'date' => $date,
                'payer_name' => $payer,
                'flat_no' => $flat,
                'receipt_type' => $type,
                'reference_no' => $ref,
                'mode_of_payment' => $mode,
                'amount' => $amount,
                'account_id' => $account,
                'location' => 'Tower A',
                'status' => 'completed',
            ]);
        }

        Receipt::factory()
            ->count(240)
            ->state(fn () => [
                'account_id' => $accounts->random(),
                'date' => fake()->dateTimeBetween('2025-04-01', '2025-05-26'),
            ])
            ->create(['society_id' => $societyId]);
    }
}
