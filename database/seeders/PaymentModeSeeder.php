<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMode;

class PaymentModeSeeder extends Seeder
{
    public function run(): void
    {
        $modes = [
            ['name' => 'Cash', 'description' => 'Cash payment'],
            ['name' => 'Cheque', 'description' => 'Payment via cheque'],
            ['name' => 'Bank Transfer', 'description' => 'NEFT / RTGS / IMPS'],
            ['name' => 'UPI', 'description' => 'UPI (PhonePe, GPay, Paytm, etc.)'],
            ['name' => 'Credit Card', 'description' => 'Credit card payment'],
            ['name' => 'Debit Card', 'description' => 'Debit card payment'],
            ['name' => 'Net Banking', 'description' => 'Internet banking'],
            ['name' => 'Other', 'description' => 'Any other mode'],
        ];

        foreach ($modes as $mode) {
            PaymentMode::create($mode);
        }
    }
}
