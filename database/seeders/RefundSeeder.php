<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Refund;
use App\Models\Payment;

class RefundSeeder extends Seeder
{
    public function run(): void
    {
        $payments = Payment::take(5)->get();
        $statuses = ['completed', 'completed', 'completed', 'pending', 'failed'];

        foreach ($payments as $index => $payment) {
            Refund::create([
                'refund_number' => 'RFND-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'payment_id' => $payment->id,
                'society_id' => $payment->society_id,
                'member_name' => $payment->member_name,
                'flat_number' => $payment->flat_number,
                'amount' => $payment->amount,
                'refund_method' => $payment->payment_method,
                'refund_date' => now()->subDays($index + 1),
                'status' => $statuses[$index],
                'reason' => 'Overpayment adjustment',
            ]);
        }
    }
}
