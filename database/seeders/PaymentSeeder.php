<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = Invoice::where('status', 'paid')->get();
        $methods = ['UPI', 'Bank Transfer', 'Credit Card', 'Net Banking', 'Cash'];

        foreach ($invoices as $index => $invoice) {
            Payment::create([
                'receipt_number' => 'RCPT-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'invoice_id' => $invoice->id,
                'society_id' => $invoice->society_id,
                'member_name' => $invoice->member_name,
                'flat_number' => $invoice->flat_number,
                'amount' => $invoice->total_amount,
                'payment_method' => $methods[$index % 5],
                'transaction_id' => 'TXN' . str_pad(rand(100000, 999999), 10, '0', STR_PAD_LEFT),
                'payment_date' => $invoice->invoice_date->addDays(rand(1, 5)),
                'status' => 'success',
            ]);
        }
    }
}
