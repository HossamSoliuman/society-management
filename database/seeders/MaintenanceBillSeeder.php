<?php

namespace Database\Seeders;

use App\Models\MaintenanceBill;
use App\Models\MaintenanceBillItem;
use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class MaintenanceBillSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();
        $societyId = $society?->id;

        $members = Member::query()->when($societyId, fn ($q) => $q->where('society_id', $societyId))->get();
        $units = Unit::query()->when($societyId, fn ($q) => $q->where('society_id', $societyId))->get();

        $this->seedDemoBills($societyId, $members, $units);
        $this->seedFillerBills($societyId, $members, $units);
    }

    /**
     * The exact 10 rows shown on page 1 of the Bill List (design §3.5).
     *
     * @param  Collection<int, Member>  $members
     * @param  Collection<int, Unit>  $units
     */
    private function seedDemoBills(?int $societyId, $members, $units): void
    {
        // [number, month, bill_date, cycle, tower, total, collected, status, due_date]
        $rows = [
            ['MB-2025-000156', 'June 2025', '2025-05-30', 'June 2025', 'Tower A', 3500, 3500, 'paid', '2025-06-15'],
            ['MB-2025-000155', 'June 2025', '2025-05-30', 'June 2025', 'Tower B', 3500, 0, 'pending', '2025-06-15'],
            ['MB-2025-000154', 'June 2025', '2025-05-30', 'June 2025', 'Tower C', 3500, 1500, 'partial', '2025-06-15'],
            ['MB-2025-000153', 'June 2025', '2025-05-30', 'June 2025', 'Tower D', 3500, 0, 'overdue', '2025-06-10'],
            ['MB-2025-000152', 'May 2025', '2025-04-30', 'May 2025', 'Tower A', 3300, 3300, 'paid', '2025-05-15'],
            ['MB-2025-000151', 'May 2025', '2025-04-30', 'May 2025', 'Tower B', 3300, 0, 'pending', '2025-05-15'],
            ['MB-2025-000150', 'May 2025', '2025-04-30', 'May 2025', 'Tower C', 3300, 3300, 'paid', '2025-05-15'],
            ['MB-2025-000149', 'Apr 2025', '2025-03-30', 'Apr 2025', 'Tower D', 3100, 0, 'overdue', '2025-04-05'],
            ['MB-2025-000148', 'Apr 2025', '2025-03-30', 'Apr 2025', 'Tower A', 3100, 3100, 'paid', '2025-04-15'],
            ['MB-2025-000147', 'Apr 2025', '2025-03-30', 'Apr 2025', 'Tower B', 3100, 1100, 'partial', '2025-04-15'],
        ];

        foreach ($rows as $index => [$number, $month, $billDate, $cycle, $tower, $total, $collected, $status, $due]) {
            $member = $members->get($index);
            $unit = $units->get($index);

            $bill = MaintenanceBill::create([
                'society_id' => $societyId,
                'bill_number' => $number,
                'member_id' => $member?->id,
                'unit_id' => $unit?->id,
                'member_name' => $member->name ?? 'Mr. Ramesh Sharma',
                'flat_number' => $unit->unit_number ?? 'A-101',
                'tower_wing' => $tower,
                'floor' => $unit->floor ?? '1st Floor',
                'bill_month' => $month,
                'bill_date' => $billDate,
                'due_date' => $due,
                'bill_cycle' => $cycle,
                'billing_type' => 'Monthly Maintenance',
                'sub_total' => $total,
                'total_amount' => $total,
                'collected_amount' => $collected,
                'outstanding_amount' => $total - $collected,
                'status' => $status,
                'send_email' => true,
                'send_sms' => true,
                'send_whatsapp' => false,
            ]);

            $this->seedItems($bill, $total);
        }
    }

    /**
     * Standard 4-line breakdown that sums to the bill sub-total.
     */
    private function seedItems(MaintenanceBill $bill, float $total): void
    {
        // Base breakdown = 3,500 (2500 + 500 + 300 + 200); scale "Others" for lower totals.
        $others = max(0, $total - 3300);
        $rows = [
            ['Maintenance Charges', 'Monthly maintenance charges', 2500],
            ['Sinking Fund', 'Sinking fund contribution', 500],
            ['Reserve Fund', 'Reserve fund contribution', 300],
            ['Others', 'Water tank cleaning charges', $others],
        ];

        foreach ($rows as $sort => [$name, $description, $amount]) {
            $bill->items()->create([
                'charge_head_name' => $name,
                'description' => $description,
                'amount' => $amount,
                'sort_order' => $sort + 1,
            ]);
        }
    }

    /**
     * 146 additional bills so the list totals 156 (matches the design pagination).
     *
     * @param  Collection<int, Member>  $members
     * @param  Collection<int, Unit>  $units
     */
    private function seedFillerBills(?int $societyId, $members, $units): void
    {
        for ($number = 146; $number >= 1; $number--) {
            $member = $members->isNotEmpty() ? $members->random() : null;
            $unit = $units->isNotEmpty() ? $units->random() : null;

            MaintenanceBill::factory()
                ->has(MaintenanceBillItem::factory()->count(4), 'items')
                ->create([
                    'society_id' => $societyId,
                    'bill_number' => 'MB-2025-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT),
                    'member_id' => $member?->id,
                    'unit_id' => $unit?->id,
                    'member_name' => $member->name ?? null,
                    'flat_number' => $unit->unit_number ?? null,
                ]);
        }
    }
}
