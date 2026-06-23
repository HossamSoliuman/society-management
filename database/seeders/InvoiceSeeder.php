<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Society;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $societies = Society::all();
        $types = ['Maintenance', 'Sinking Fund', 'Water Charges', 'Parking Charges', 'Other Charges'];
        $statuses = ['paid', 'pending', 'overdue', 'paid', 'pending'];
        $members = [
            ['name' => 'Rahul Mehta', 'flat' => 'A-101'],
            ['name' => 'Priya Sharma', 'flat' => 'B-204'],
            ['name' => 'Amit Verma', 'flat' => 'C-302'],
            ['name' => 'Neha Kapoor', 'flat' => 'D-401'],
            ['name' => 'Suresh Nair', 'flat' => 'E-502'],
            ['name' => 'Kavita Joshi', 'flat' => 'A-203'],
            ['name' => 'Vikram Iyer', 'flat' => 'C-101'],
            ['name' => 'Pooja Singh', 'flat' => 'B-103'],
        ];

        foreach ($societies as $society) {
            for ($i = 0; $i < 8; $i++) {
                $amount = match($types[$i % 5]) {
                    'Maintenance' => 4250.00,
                    'Sinking Fund' => 2000.00,
                    'Water Charges' => 750.00,
                    'Parking Charges' => 1200.00,
                    default => 500.00,
                };

                $status = $statuses[$i % 5];
                $paidAmount = $status === 'paid' ? $amount : ($status === 'partially_paid' ? $amount * 0.5 : 0);

                Invoice::create([
                    'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($society->id * 100 + $i + 1, 4, '0', STR_PAD_LEFT),
                    'society_id' => $society->id,
                    'member_name' => $members[$i]['name'],
                    'flat_number' => $members[$i]['flat'],
                    'building_name' => 'Tower ' . chr(65 + ($i % 5)),
                    'invoice_type' => $types[$i % 5],
                    'invoice_date' => now()->subDays($i * 2),
                    'due_date' => now()->addDays(15 - $i),
                    'amount' => $amount,
                    'tax_amount' => $amount * 0.18,
                    'total_amount' => $amount * 1.18,
                    'paid_amount' => $paidAmount,
                    'outstanding_amount' => ($amount * 1.18) - $paidAmount,
                    'status' => $status,
                ]);
            }
        }
    }
}
