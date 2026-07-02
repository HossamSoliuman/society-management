<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Society;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');

        $categories = ExpenseCategory::query()
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->get()->keyBy('name');
        $vendors = Vendor::query()
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->get()->keyBy('name');

        $this->seedDemoExpenses($societyId, $categories, $vendors);
        $this->seedFillerExpenses($societyId, $categories, $vendors);
    }

    /**
     * The exact 8 rows on page 1 of the All Expenses table. See "expenses.png".
     * "Pest Control Services" maps to the real "Pest Control" category (rendered as a purple pill).
     *
     * @param  Collection<string, ExpenseCategory>  $categories
     * @param  Collection<string, Vendor>  $vendors
     */
    private function seedDemoExpenses(?int $societyId, $categories, $vendors): void
    {
        // [code, date, title, category, vendor, amount, mode, status]
        $rows = [
            ['EXP-2024-056', '2024-05-31', 'Electricity Bill – May 2024', 'Utilities', 'MSEDCL', 18750, 'Net Banking', 'paid'],
            ['EXP-2024-055', '2024-05-30', 'Housekeeping Salary – May', 'Salary', 'Shri Sai Services', 24000, 'Cash', 'paid'],
            ['EXP-2024-054', '2024-05-29', 'Lift Maintenance – May', 'Maintenance', 'Otis Elevator Co.', 15900, 'Cheque', 'pending'],
            ['EXP-2024-053', '2024-05-28', 'Garden Maintenance', 'Maintenance', 'Green Earth Pvt. Ltd.', 8500, 'UPI', 'paid'],
            ['EXP-2024-052', '2024-05-27', 'Pest Control Services', 'Pest Control', 'Rentokil Initial', 6950, 'Card', 'paid'],
            ['EXP-2024-051', '2024-05-25', 'Plumbing Repair', 'Repairs', 'Om Plumbing', 4350, 'Cash', 'paid'],
            ['EXP-2024-050', '2024-05-24', 'Paint Purchase', 'Purchase', 'Nirman Hardware', 12450, 'Card', 'pending'],
            ['EXP-2024-049', '2024-05-22', 'Security Salary – May', 'Salary', 'Safe Guard Pvt. Ltd.', 28000, 'Bank Transfer', 'paid'],
        ];

        foreach ($rows as [$code, $date, $title, $categoryName, $vendorName, $amount, $mode, $status]) {
            $paid = $status === 'paid' ? $amount : 0;

            Expense::create([
                'society_id' => $societyId,
                'code' => $code,
                'expense_date' => $date,
                'title' => $title,
                'category_id' => $categories->get($categoryName)?->id,
                'vendor_id' => $vendors->get($vendorName)?->id,
                'reference_no' => $code,
                'payment_mode' => $mode,
                'amount' => $amount,
                'tax_amount' => 0,
                'paid_amount' => $paid,
                'due_amount' => $amount - $paid,
                'bill_date' => $date,
                'payment_status' => $status,
                'expense_for' => 'Society',
            ]);
        }
    }

    /**
     * 56 filler rows (older than the page-1 dates) so the table totals 64 entries.
     *
     * @param  Collection<string, ExpenseCategory>  $categories
     * @param  Collection<string, Vendor>  $vendors
     */
    private function seedFillerExpenses(?int $societyId, $categories, $vendors): void
    {
        $palette = [
            ['Utilities', 'MSEDCL', 'Net Banking'],
            ['Salary', 'Shri Sai Services', 'Cash'],
            ['Maintenance', 'Otis Elevator Co.', 'Cheque'],
            ['Pest Control', 'Rentokil Initial', 'Card'],
            ['Repairs', 'Om Plumbing', 'UPI'],
            ['Purchase', 'Nirman Hardware', 'Card'],
            ['Security', 'Safe Guard Pvt. Ltd.', 'Bank Transfer'],
            ['Cleaning', 'Sparkle Cleaners', 'Cash'],
        ];

        $start = Carbon::parse('2024-05-20');

        for ($i = 1; $i <= 56; $i++) {
            [$categoryName, $vendorName, $mode] = $palette[($i - 1) % count($palette)];
            $amount = 2000 + (($i * 137) % 40000);
            // 3 pending + 2 overdue among the fillers; the rest paid.
            $status = match (true) {
                $i <= 3 => 'pending',
                $i <= 5 => 'overdue',
                default => 'paid',
            };
            $paid = $status === 'paid' ? $amount : 0;

            Expense::create([
                'society_id' => $societyId,
                'code' => 'EXP-2024-'.str_pad((string) (200 + $i), 3, '0', STR_PAD_LEFT),
                'expense_date' => $start->copy()->subDays($i),
                'title' => $categoryName.' Expense',
                'category_id' => $categories->get($categoryName)?->id,
                'vendor_id' => $vendors->get($vendorName)?->id,
                'reference_no' => 'REF-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'payment_mode' => $mode,
                'amount' => $amount,
                'tax_amount' => 0,
                'paid_amount' => $paid,
                'due_amount' => $amount - $paid,
                'bill_date' => $start->copy()->subDays($i),
                'payment_status' => $status,
                'expense_for' => 'Society',
            ]);
        }
    }
}
