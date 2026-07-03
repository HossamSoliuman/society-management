<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountingPayment;
use App\Models\Society;
use Illuminate\Database\Seeder;

class AccountingPaymentSeeder extends Seeder
{
    /**
     * Seed vendor/expense payments for the Payments tab (C7). A handful of named
     * rows plus factory fill so the list paginates.
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        $accounts = Account::pluck('id', 'code');
        $hdfc = $accounts['1122'] ?? null;
        $sbi = $accounts['1121'] ?? null;
        $cash = $accounts['1110'] ?? null;

        // [payment_no, date, payee, purpose, mode, amount, account]
        $named = [
            ['PMT/25-26/0086', '2025-05-30 15:00:00', 'CleanPro Services', 'Housekeeping Charges', 'Net Banking', 18000, $hdfc],
            ['PMT/25-26/0085', '2025-05-29 12:30:00', 'MSEB', 'Electricity Bill - May', 'Net Banking', 42000, $hdfc],
            ['PMT/25-26/0084', '2025-05-28 11:00:00', 'AquaFresh Tankers', 'Water Tanker Supply', 'Cash', 6000, $cash],
            ['PMT/25-26/0083', '2025-05-27 16:45:00', 'SecureGuard Pvt Ltd', 'Security Services', 'Net Banking', 55000, $sbi],
            ['PMT/25-26/0082', '2025-05-26 10:20:00', 'GreenScape', 'Garden Maintenance', 'UPI', 8500, $sbi],
            ['PMT/25-26/0081', '2025-05-25 14:10:00', 'LiftCare India', 'Lift AMC', 'Cheque', 12000, $hdfc],
        ];

        foreach ($named as [$no, $date, $payee, $purpose, $mode, $amount, $account]) {
            AccountingPayment::create([
                'society_id' => $societyId,
                'payment_no' => $no,
                'date' => $date,
                'payee' => $payee,
                'purpose' => $purpose,
                'mode' => $mode,
                'amount' => $amount,
                'account_id' => $account,
                'status' => 'completed',
            ]);
        }

        AccountingPayment::factory()
            ->count(80)
            ->state(fn () => [
                'account_id' => $accounts->random(),
                'date' => fake()->dateTimeBetween('2025-04-01', '2025-05-24'),
            ])
            ->create(['society_id' => $societyId]);
    }
}
