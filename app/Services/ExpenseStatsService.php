<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseBudget;
use App\Models\ExpenseCategory;
use App\Models\Society;
use Illuminate\Support\Carbon;

/**
 * Query-backed figures for the Expenses module (stat cards, donuts, budgets).
 */
class ExpenseStatsService
{
    private const PALETTE = ['#F97316', '#3B82F6', '#10B981', '#8B5CF6', '#EF4444', '#14B8A6', '#F59E0B', '#94a3b8'];

    /**
     * @return array<string, mixed>
     */
    public function kpis(Society $society): array
    {
        $now = Carbon::now();
        $base = fn () => Expense::query()->forSociety($society)->where('payment_status', '!=', 'cancelled');

        $month = (float) $base()->whereBetween('expense_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('amount');
        $lastMonth = (float) $base()->whereBetween('expense_date', [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount');
        $year = (float) $base()->whereBetween('expense_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->sum('amount');
        $lastYear = (float) $base()->whereBetween('expense_date', [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()])->sum('amount');
        $pendingQuery = $base()->whereIn('payment_status', ['pending', 'overdue']);
        $pending = (float) (clone $pendingQuery)->sum('due_amount');
        $pendingBills = (clone $pendingQuery)->count();

        $budget = $this->yearlyBudget($society, (int) $now->year);
        $budgetUsed = $budget > 0 ? (int) min(100, round($year / $budget * 100)) : 0;

        $trend = fn (float $now, float $then, string $label) => $then > 0
            ? round(abs($now - $then) / $then * 100, 1).'% '.($now >= $then ? 'more' : 'less').' vs '.$label
            : 'No '.$label.' data';

        return [
            'month_total' => $month,
            'month_trend' => $trend($month, $lastMonth, 'last month'),
            'month_up' => $month > $lastMonth,
            'year_total' => $year,
            'year_trend' => $trend($year, $lastYear, 'last year'),
            'year_up' => $year > $lastYear,
            'pending' => $pending,
            'pending_bills' => $pendingBills,
            'budget' => $budget,
            'budget_used_pct' => $budgetUsed,
        ];
    }

    /**
     * Total budget for a year: explicit overall row, else the sum of category rows.
     */
    public function yearlyBudget(Society $society, int $year): float
    {
        $overall = ExpenseBudget::query()->forSociety($society)->where('year', $year)->whereNull('expense_category_id')->value('amount');
        if ($overall !== null) {
            return (float) $overall;
        }

        return (float) ExpenseBudget::query()->forSociety($society)->where('year', $year)->whereNotNull('expense_category_id')->sum('amount');
    }

    /**
     * Amount spent per category in a period, with share and colour.
     *
     * @return array<int, array{category_id: ?int, label: string, value: float, amount: string, pct: string, width: int, color: string}>
     */
    public function byCategory(Society $society, ?Carbon $from = null, ?Carbon $to = null, int $limit = 8): array
    {
        $rows = Expense::query()
            ->forSociety($society)
            ->where('payment_status', '!=', 'cancelled')
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->selectRaw('category_id, COALESCE(SUM(amount),0) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        $names = ExpenseCategory::query()->forSociety($society)->pluck('name', 'id');
        $total = (float) $rows->sum('total');
        $out = [];
        foreach ($rows->take($limit)->values() as $i => $row) {
            $value = (float) $row->total;
            $pct = $total > 0 ? round($value / $total * 100, 1) : 0;
            $out[] = [
                'category_id' => $row->category_id,
                'label' => $names[$row->category_id] ?? 'Uncategorised',
                'value' => $value,
                'amount' => '&#8377; '.number_format($value),
                'pct' => $pct.'%',
                'width' => (int) round($pct),
                'color' => self::PALETTE[$i % count(self::PALETTE)],
            ];
        }

        return $out;
    }

    /**
     * Donut shape shared by the expenses index and the categories page.
     *
     * @return array<string, mixed>
     */
    public function categoryDonut(Society $society, ?Carbon $from = null, ?Carbon $to = null, string $centerLabel = 'Total'): array
    {
        $segments = $this->byCategory($society, $from, $to);

        return [
            'center_value' => '&#8377; '.number_format(array_sum(array_column($segments, 'value'))),
            'center_label' => $centerLabel,
            'segments' => $segments,
        ];
    }

    /**
     * Budget used per category for the current year.
     *
     * @return array<int, array{category_id: int, budget: float, spent: float, pct: int}>
     */
    public function budgetUsage(Society $society, int $year): array
    {
        $budgets = ExpenseBudget::query()->forSociety($society)->where('year', $year)->whereNotNull('expense_category_id')->pluck('amount', 'expense_category_id');
        $spent = Expense::query()->forSociety($society)->where('payment_status', '!=', 'cancelled')
            ->whereYear('expense_date', $year)
            ->selectRaw('category_id, COALESCE(SUM(amount),0) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $out = [];
        foreach ($budgets as $categoryId => $amount) {
            $used = (float) ($spent[$categoryId] ?? 0);
            $out[(int) $categoryId] = [
                'category_id' => (int) $categoryId,
                'budget' => (float) $amount,
                'spent' => $used,
                'pct' => (float) $amount > 0 ? (int) min(100, round($used / (float) $amount * 100)) : 0,
            ];
        }

        return $out;
    }
}
