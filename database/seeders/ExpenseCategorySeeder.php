<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Society;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');

        // [name, icon, color, description, status, created_on] — the exact 12 rows behind the
        // Expense Categories screen (10 on page 1, 2 on page 2). See "expense category list.png".
        $rows = [
            ['Maintenance', 'fa-screwdriver-wrench', 'purple', 'Repairs and maintenance of building and common areas', 'active', '2024-01-01'],
            ['Utilities', 'fa-bolt', 'blue', 'Electricity, Water, Gas and other utility expenses', 'active', '2024-01-01'],
            ['Salary', 'fa-users', 'green', 'Staff salary and related payments', 'active', '2024-01-01'],
            ['Security', 'fa-shield-halved', 'orange', 'Security guard salary and security related expenses', 'active', '2024-01-02'],
            ['Cleaning', 'fa-broom', 'pink', 'Cleaning staff salary and cleaning material', 'active', '2024-01-02'],
            ['Admin Expenses', 'fa-file-lines', 'blue', 'Office expenses, stationery, printing, etc.', 'active', '2024-01-03'],
            ['Lift Maintenance', 'fa-elevator', 'teal', 'Lift AMC and repair expenses', 'active', '2024-01-03'],
            ['Garden Maintenance', 'fa-leaf', 'green', 'Garden and plantation maintenance', 'inactive', '2024-02-10'],
            ['Pest Control', 'fa-bug', 'blue', 'Pest control and fumigation expenses', 'active', '2024-02-15'],
            ['Miscellaneous', 'fa-ellipsis', 'gray', 'Other miscellaneous expenses', 'inactive', '2024-03-20'],
            ['Repairs', 'fa-wrench', 'yellow', 'Repair works, plumbing and electrical fixes', 'active', '2024-03-25'],
            ['Purchase', 'fa-cart-shopping', 'teal', 'Material and equipment purchases', 'active', '2024-03-28'],
        ];

        foreach ($rows as $order => [$name, $icon, $color, $description, $status, $createdOn]) {
            $category = ExpenseCategory::create([
                'society_id' => $societyId,
                'name' => $name,
                'slug' => Str::slug($name),
                'icon' => $icon,
                'color' => $color,
                'description' => $description,
                'status' => $status,
                'display_order' => $order + 1,
                'applicable_for' => 'all_buildings',
            ]);

            $timestamp = Carbon::parse($createdOn.' 09:00:00');
            $category->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->saveQuietly();
        }
    }
}
