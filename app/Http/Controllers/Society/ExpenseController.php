<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Society;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /** Payment modes offered in the Add/Edit form and the mode filter. */
    private const PAYMENT_MODES = ['Cash', 'Card', 'UPI', 'Net Banking', 'Cheque', 'Bank Transfer'];

    /** Scope options for the "Expense For" select. */
    private const EXPENSE_FOR = ['Building', 'Wing', 'Flat', 'Society'];

    public function index(Request $request): View
    {
        $society = Society::first();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabStatus = in_array($tab, ['paid', 'pending', 'overdue', 'cancelled'], true) ? $tab : null;

        $expenses = Expense::query()
            ->with(['category', 'vendor'])
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($tabStatus, fn ($q) => $q->where('payment_status', $tabStatus))
            ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('vendor'), fn ($q) => $q->where('vendor_id', $request->integer('vendor')))
            ->when($request->filled('mode'), fn ($q) => $q->where('payment_mode', $request->string('mode')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('reference_no', 'like', "%{$term}%")
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('society.expenses.index', [
            'society' => $society,
            'expenses' => $expenses,
            'tab' => $tab,
            'kpis' => $this->kpis(),
            'overview' => $this->overviewDonut(),
            'topCategories' => $this->topCategories(),
            'categories' => $this->activeCategories($society),
            'vendors' => $this->activeVendors($society),
            'paymentModes' => self::PAYMENT_MODES,
        ]);
    }

    public function create(): View
    {
        $society = Society::first();

        return view('society.expenses.create', [
            'society' => $society,
            'categories' => $this->activeCategories($society),
            'vendors' => $this->activeVendors($society),
            'paymentModes' => self::PAYMENT_MODES,
            'expenseForOptions' => self::EXPENSE_FOR,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();
        $data = $this->validateExpense($request);

        $expense = Expense::create($this->expensePayload($data, $society) + [
            'code' => $this->nextCode($data['expense_date']),
        ]);

        if ($request->boolean('save_and_add')) {
            return redirect()->route('society.expenses.create')
                ->with('success', "Expense {$expense->code} saved. Add another below.");
        }

        return redirect()->route('society.expenses.index')
            ->with('success', "Expense {$expense->code} saved successfully.");
    }

    public function edit(Expense $expense): View
    {
        $society = Society::first();

        return view('society.expenses.edit', [
            'society' => $society,
            'expense' => $expense,
            'categories' => $this->activeCategories($society),
            'vendors' => $this->activeVendors($society),
            'paymentModes' => self::PAYMENT_MODES,
            'expenseForOptions' => self::EXPENSE_FOR,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $society = Society::first();
        $data = $this->validateExpense($request);

        $expense->update($this->expensePayload($data, $society));

        return redirect()->route('society.expenses.index')
            ->with('success', "Expense {$expense->code} updated successfully.");
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $code = $expense->code;
        $expense->delete();

        return redirect()->route('society.expenses.index')
            ->with('success', "Expense {$code} deleted.");
    }

    public function reports(Request $request): View
    {
        $society = Society::first();

        $query = Expense::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('vendor'), fn ($q) => $q->where('vendor_id', $request->integer('vendor')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', Carbon::parse($request->string('to'))));

        $totalAmount = (clone $query)->sum('amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalDue = (clone $query)->sum('due_amount');
        $count = (clone $query)->count();

        $breakdown = (clone $query)
            ->selectRaw('category_id, COUNT(*) as entries, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        return view('society.expenses.reports', [
            'society' => $society,
            'categories' => $this->activeCategories($society),
            'vendors' => $this->activeVendors($society),
            'summary' => [
                'total' => $totalAmount,
                'paid' => $totalPaid,
                'due' => $totalDue,
                'count' => $count,
            ],
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Validate the Add/Edit expense form.
     *
     * @return array<string, mixed>
     */
    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'bill_date' => ['nullable', 'date'],
            'payment_status' => ['required', 'in:paid,pending,overdue,cancelled'],
            'expense_for' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'attachment_notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /**
     * Build the persisted attributes (computes due = amount + tax - paid, stores the attachment).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function expensePayload(array $data, ?Society $society): array
    {
        $amount = (float) $data['amount'];
        $tax = (float) ($data['tax_amount'] ?? 0);
        $paid = (float) $data['paid_amount'];

        $payload = [
            'society_id' => $society?->id,
            'expense_date' => $data['expense_date'],
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'vendor_id' => $data['vendor_id'],
            'reference_no' => $data['reference_no'] ?? null,
            'payment_mode' => $data['payment_mode'],
            'amount' => $amount,
            'tax_amount' => $tax,
            'paid_amount' => $paid,
            'due_amount' => max(0, ($amount + $tax) - $paid),
            'bill_date' => $data['bill_date'] ?? null,
            'payment_status' => $data['payment_status'],
            'expense_for' => $data['expense_for'] ?? null,
            'description' => $data['description'] ?? null,
            'attachment_notes' => $data['attachment_notes'] ?? null,
        ];

        if (isset($data['attachment']) && request()->hasFile('attachment')) {
            $payload['attachment_path'] = request()->file('attachment')->store('expense-attachments', 'public');
        }

        return $payload;
    }

    private function nextCode(string $expenseDate): string
    {
        $year = Carbon::parse($expenseDate)->format('Y');
        $next = (Expense::max('id') ?? 0) + 1;

        return 'EXP-'.$year.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return Collection<int, ExpenseCategory>
     */
    private function activeCategories(?Society $society)
    {
        return ExpenseCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Vendor>
     */
    private function activeVendors(?Society $society)
    {
        return Vendor::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Demo KPI figures matching the stat cards in "expenses.png".
     *
     * @return array<string, mixed>
     */
    private function kpis(): array
    {
        return [
            'month_total' => 82450,
            'month_trend' => '12.5% vs last month',
            'year_total' => 945600,
            'year_trend' => '8.3% vs last year',
            'pending' => 125000,
            'pending_bills' => 5,
            'budget' => 150000,
            'budget_used_pct' => 55,
        ];
    }

    /**
     * Expense Overview donut + legend (rail card 1 in "expenses.png").
     *
     * @return array<string, mixed>
     */
    private function overviewDonut(): array
    {
        return [
            'center_value' => '&#8377; 82,450',
            'center_label' => 'Total',
            'segments' => [
                ['label' => 'Utilities', 'amount' => '&#8377; 18,750', 'pct' => '22.8%', 'value' => 18750, 'color' => '#3B82F6'],
                ['label' => 'Salary', 'amount' => '&#8377; 52,000', 'pct' => '63.1%', 'value' => 52000, 'color' => '#10B981'],
                ['label' => 'Maintenance', 'amount' => '&#8377; 11,900', 'pct' => '14.4%', 'value' => 11900, 'color' => '#F97316'],
                ['label' => 'Others', 'amount' => '&#8377; –', 'pct' => '0%', 'value' => 0, 'color' => '#8B5CF6'],
            ],
        ];
    }

    /**
     * Top Categories progress rows (rail card 2 in "expenses.png").
     *
     * @return array<int, array<string, mixed>>
     */
    private function topCategories(): array
    {
        return [
            ['label' => 'Salary', 'amount' => '&#8377; 52,000', 'pct' => '63.1%', 'width' => 63, 'color' => 'var(--success)'],
            ['label' => 'Utilities', 'amount' => '&#8377; 18,750', 'pct' => '22.8%', 'width' => 23, 'color' => 'var(--info)'],
            ['label' => 'Maintenance', 'amount' => '&#8377; 11,900', 'pct' => '14.4%', 'width' => 14, 'color' => 'var(--orange)'],
        ];
    }
}
