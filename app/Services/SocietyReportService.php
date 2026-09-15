<?php

namespace App\Services;

use App\Models\CollectionPayment;
use App\Models\Expense;
use App\Models\MaintenanceBill;
use App\Models\Society;
use Illuminate\Support\Carbon;

/**
 * Data behind the society-level Collection / Expense / Defaulter reports.
 * Each report returns the same shape so the screen, Excel and PDF share it:
 * ['title', 'headings', 'rows', 'summary' => [label => value], 'period'].
 */
class SocietyReportService
{
    /**
     * @return array<string, mixed>
     */
    public function collection(Society $society, Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = CollectionPayment::query()
            ->forSociety($society)
            ->whereBetween('receipt_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($filters['mode'] ?? null, fn ($q, $mode) => $q->where('payment_mode', $mode))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('receipt_date');

        $payments = $query->get();
        $byMode = $payments->groupBy(fn ($p) => $p->paymentModeLabel())->map(fn ($g) => (float) $g->sum('paid_amount'));

        return [
            'title' => 'Collection Report',
            'period' => [$from->toDateString(), $to->toDateString()],
            'headings' => ['Receipt No', 'Date', 'Member', 'Flat', 'Bill Period', 'Mode', 'Total Due', 'Paid', 'Discount', 'Balance', 'Status', 'Collected By'],
            'rows' => $payments->map(fn (CollectionPayment $p) => [
                $p->receipt_number,
                $p->receipt_date?->format('d M Y'),
                $p->member_name,
                $p->flat_number,
                $p->bill_period,
                $p->paymentModeLabel(),
                (float) $p->total_due,
                (float) $p->paid_amount,
                (float) $p->discount,
                (float) $p->balance_due,
                ucfirst($p->status),
                $p->collected_by,
            ])->all(),
            'summary' => [
                'Receipts' => $payments->count(),
                'Total Collected' => format_inr($payments->sum('paid_amount')),
                'Discounts' => format_inr($payments->sum('discount')),
                'Online' => format_inr($payments->where('is_online', true)->sum('paid_amount')),
            ],
            'breakdown' => $byMode->map(fn ($v, $k) => ['label' => $k, 'value' => $v])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function expense(Society $society, Carbon $from, Carbon $to, array $filters = []): array
    {
        $expenses = Expense::query()
            ->with(['category', 'vendor'])
            ->forSociety($society)
            ->whereDate('expense_date', '>=', $from->toDateString())
            ->whereDate('expense_date', '<=', $to->toDateString())
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('payment_status', $status))
            ->orderBy('expense_date')
            ->get();

        $byCategory = $expenses->groupBy(fn ($e) => $e->category?->name ?? 'Uncategorised')->map(fn ($g) => (float) $g->sum('amount'));

        return [
            'title' => 'Expense Report',
            'period' => [$from->toDateString(), $to->toDateString()],
            'headings' => ['Code', 'Date', 'Title', 'Category', 'Vendor', 'Mode', 'Amount', 'Tax', 'Paid', 'Due', 'Status'],
            'rows' => $expenses->map(fn (Expense $e) => [
                $e->code,
                $e->expense_date?->format('d M Y'),
                $e->title,
                $e->category?->name,
                $e->vendor?->name,
                $e->payment_mode,
                (float) $e->amount,
                (float) $e->tax_amount,
                (float) $e->paid_amount,
                (float) $e->due_amount,
                ucfirst($e->payment_status),
            ])->all(),
            'summary' => [
                'Entries' => $expenses->count(),
                'Total Amount' => format_inr($expenses->sum('amount')),
                'Paid' => format_inr($expenses->sum('paid_amount')),
                'Outstanding' => format_inr($expenses->sum('due_amount')),
            ],
            'breakdown' => $byCategory->sortDesc()->map(fn ($v, $k) => ['label' => $k, 'value' => $v])->values()->all(),
        ];
    }

    /**
     * Members / units with unpaid bills as on a date, oldest due first.
     *
     * @return array<string, mixed>
     */
    public function defaulters(Society $society, Carbon $asOn, array $filters = []): array
    {
        $minDays = (int) ($filters['min_days'] ?? 0);

        $bills = MaintenanceBill::query()
            ->forSociety($society)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('outstanding_amount', '>', 0)
            ->whereDate('due_date', '<=', $asOn->copy()->subDays($minDays)->toDateString())
            ->when($filters['tower'] ?? null, fn ($q, $tower) => $q->where('tower_wing', $tower))
            ->orderBy('due_date')
            ->get();

        $grouped = $bills->groupBy(fn ($b) => ($b->member_id ?: 'u'.$b->unit_id).'|'.$b->flat_number);

        $rows = $grouped->map(function ($group) use ($asOn) {
            $first = $group->first();
            $oldest = $group->min('due_date');

            return [
                $first->member_name ?: '—',
                $first->flat_number ?: '—',
                $first->tower_wing ?: '',
                $group->count(),
                $group->map(fn ($b) => $b->bill_month)->unique()->implode(', '),
                Carbon::parse($oldest)->format('d M Y'),
                (int) Carbon::parse($oldest)->diffInDays($asOn),
                (float) $group->sum('total_amount'),
                (float) $group->sum('collected_amount'),
                (float) $group->sum('outstanding_amount'),
            ];
        })->sortByDesc(fn ($r) => $r[9])->values()->all();

        return [
            'title' => 'Defaulter Report',
            'period' => [$asOn->toDateString(), $asOn->toDateString()],
            'headings' => ['Member', 'Flat', 'Tower', 'Bills', 'Periods', 'Oldest Due', 'Days Overdue', 'Billed', 'Paid', 'Outstanding'],
            'rows' => $rows,
            'summary' => [
                'Defaulters' => count($rows),
                'Unpaid Bills' => $bills->count(),
                'Total Outstanding' => format_inr($bills->sum('outstanding_amount')),
                'As On' => $asOn->format('d M Y'),
            ],
            'breakdown' => [],
        ];
    }
}
