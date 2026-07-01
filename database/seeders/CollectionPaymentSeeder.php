<?php

namespace Database\Seeders;

use App\Models\CollectionPayment;
use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CollectionPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();
        $societyId = $society?->id;

        $units = Unit::query()->when($societyId, fn ($q) => $q->where('society_id', $societyId))->get()->keyBy('unit_number');
        $members = Member::query()->when($societyId, fn ($q) => $q->where('society_id', $societyId))->get();

        $this->seedDemoPayments($societyId, $units, $members);
        $this->seedFillerPayments($societyId);
    }

    /**
     * The exact 8 rows on page 1 of the Payment Collection table (design §3.5).
     */
    private function seedDemoPayments(?int $societyId, $units, $members): void
    {
        // [receipt, date-time, name, mobile, email, flat, unit_type, period, total, paid, due, mode, status, collected_by, is_online]
        $rows = [
            ['RCPT-2024-1256', '2024-05-31 10:30:00', 'Rahul Sharma', '9876543210', 'rahul.sharma@email.com', 'A-101', '2 BHK', 'May 2024', 2850, 2850, 0, 'upi', 'paid', 'Neha Patil', true],
            ['RCPT-2024-1255', '2024-05-31 09:15:00', 'Priya Sharma', '9876543211', 'priya.sharma@email.com', 'A-102', '1 BHK', 'May 2024', 2850, 1000, 1850, 'card', 'partial', 'Neha Patil', true],
            ['RCPT-2024-1254', '2024-05-30 19:45:00', 'Amit Patel', '9876543212', 'amit.patel@email.com', 'A-103', '2 BHK', 'May 2024', 2850, 2850, 0, 'net_banking', 'paid', 'Sanjay Verma', true],
            ['RCPT-2024-1253', '2024-05-30 18:20:00', 'Neha Verma', '9876543213', 'neha.verma@email.com', 'A-104', '2 BHK', 'May 2024', 2850, 0, 2850, null, 'pending', null, false],
            ['RCPT-2024-1252', '2024-05-29 17:10:00', 'Vikram Singh', '9876543216', 'vikram.singh@email.com', 'A-201', '2 BHK', 'May 2024', 3150, 3150, 0, 'upi', 'paid', 'Neha Patil', true],
            ['RCPT-2024-1251', '2024-05-29 16:05:00', 'Sneha Iyer', '9876543220', 'sneha.iyer@email.com', 'A-202', '3 BHK', 'May 2024', 2850, 2850, 0, 'cash', 'paid', 'Sanjay Verma', false],
            ['RCPT-2024-1250', '2024-05-28 12:40:00', 'Meena Patel', '9876543215', 'meena.patel@email.com', 'A-203', '2 BHK', 'May 2024', 2850, 1500, 1350, 'card', 'partial', 'Neha Patil', true],
            ['RCPT-2024-1249', '2024-05-28 11:30:00', 'Anjali Singh', '9876543217', 'anjali.singh@email.com', 'B-101', '2 BHK', 'May 2024', 2850, 0, 2850, null, 'pending', null, false],
        ];

        foreach ($rows as $index => [$receipt, $dateTime, $name, $mobile, $email, $flat, $unitType, $period, $total, $paid, $due, $mode, $status, $by, $online]) {
            $unit = $units->get($flat);
            $member = $members->firstWhere('name', $name);

            $floor = $index < 4 ? '1st' : '2nd';
            $building = 'Building '.substr($flat, 0, 1);
            $wing = 'Wing '.substr($flat, 0, 1);

            CollectionPayment::create([
                'society_id' => $societyId,
                'receipt_number' => $receipt,
                'member_id' => $member?->id,
                'unit_id' => $unit?->id,
                'maintenance_bill_id' => null,
                'member_name' => $name,
                'member_mobile' => $mobile,
                'member_email' => $email,
                'flat_number' => $flat,
                'unit_label' => "{$flat}, {$building}, {$wing}, {$floor} Floor, {$unitType}",
                'member_code' => 'MEM-'.str_pad((string) (21 + $index), 5, '0', STR_PAD_LEFT),
                'unit_type' => $unitType,
                'bill_type' => 'Maintenance',
                'bill_period' => $period,
                'due_date' => '2024-05-31',
                'receipt_date' => $dateTime,
                'total_due' => $total,
                'paid_amount' => $paid,
                'discount' => 0,
                'fine_penalty' => 0,
                'balance_due' => $due,
                'payment_mode' => $mode,
                'reference_no' => null,
                'transaction_utr' => $mode === 'upi' ? 'UTR123456789012' : ($mode ? strtoupper(substr($mode, 0, 3)).fake()->numerify('#########') : null),
                'collected_by' => $by,
                'status' => $status,
                'is_online' => $online,
                // The first row is the fully-detailed receipt shown on the Payment Receipt screen (§5).
                'notes' => $index === 0 ? 'Paid via UPI on time.' : null,
            ]);
        }
    }

    /**
     * 116 additional (older) payments so the list totals 124 (matches the design pagination).
     */
    private function seedFillerPayments(?int $societyId): void
    {
        for ($number = 1248; $number >= 1133; $number--) {
            $date = Carbon::create(2024, fake()->numberBetween(1, 4), fake()->numberBetween(1, 28), fake()->numberBetween(9, 19), fake()->numberBetween(0, 59));

            CollectionPayment::factory()->create([
                'society_id' => $societyId,
                'receipt_number' => 'RCPT-2024-'.$number,
                'receipt_date' => $date,
                'due_date' => $date->copy()->endOfMonth(),
                'bill_period' => $date->format('F Y'),
            ]);
        }
    }
}
