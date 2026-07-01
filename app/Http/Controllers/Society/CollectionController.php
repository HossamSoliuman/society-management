<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CollectionController extends Controller
{
    /** Users offered in the "Collected By" filter. */
    private const COLLECTORS = ['Neha Patil', 'Sanjay Verma'];

    public function index(Request $request): View
    {
        return $this->renderList($request, 'society.collections.index', 'Collections');
    }

    public function online(Request $request): View
    {
        return $this->renderList($request, 'society.collections.online', 'Online Payments', onlineOnly: true);
    }

    /**
     * Shared list renderer for Payment Collection + Online Payments (same table shell).
     */
    private function renderList(Request $request, string $view, string $title, bool $onlineOnly = false): View
    {
        $society = Society::first();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabStatus = [
            'received' => 'paid',
            'pending' => 'pending',
            'overdue' => 'overdue',
            'refunded' => 'refunded',
        ][$tab] ?? null;

        $query = CollectionPayment::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($onlineOnly, fn ($q) => $q->where('is_online', true))
            ->when($tabStatus, fn ($q) => $q->where('status', $tabStatus))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('mode'), fn ($q) => $q->where('payment_mode', $request->string('mode')))
            ->when($request->filled('collected_by'), fn ($q) => $q->where('collected_by', $request->string('collected_by')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('receipt_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('receipt_date', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('member_name', 'like', "%{$term}%")
                        ->orWhere('flat_number', 'like', "%{$term}%")
                        ->orWhere('receipt_number', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('receipt_date')
            ->orderByDesc('id');

        $payments = $query->paginate(8)->withQueryString();

        return view($view, [
            'society' => $society,
            'title' => $title,
            'payments' => $payments,
            'tab' => $tab,
            'kpis' => $this->kpis(),
            'overview' => $this->overviewDonut(),
            'recent' => $this->recentTransactions($society),
            'collectors' => self::COLLECTORS,
        ]);
    }

    public function create(): View
    {
        $society = Society::first();

        $members = Member::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('unit_number')
            ->get();

        $billPeriods = MaintenanceBill::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->select('bill_month')->distinct()->pluck('bill_month');

        return view('society.collections.create', compact('society', 'members', 'units', 'billPeriods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();

        $data = $request->validate([
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'member_name' => ['nullable', 'string', 'max:255'],
            'flat_number' => ['nullable', 'string', 'max:255'],
            'unit_label' => ['nullable', 'string', 'max:255'],
            'maintenance_bill_id' => ['nullable', 'integer', 'exists:maintenance_bills,id'],
            'bill_type' => ['required', 'string', 'max:255'],
            'bill_period' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'receipt_date' => ['required', 'date'],
            'total_due' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'fine_penalty' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['required', 'in:cash,upi,card,net_banking,cheque,other'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'transaction_utr' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $totalDue = (float) $data['total_due'];
        $paid = (float) $data['paid_amount'];
        $discount = (float) ($data['discount'] ?? 0);
        $fine = (float) ($data['fine_penalty'] ?? 0);
        $balance = max(0, $totalDue - $discount - $paid);

        $status = match (true) {
            $balance <= 0 => 'paid',
            $paid > 0 => 'partial',
            default => 'pending',
        };

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('collection-attachments', 'public')
            : null;

        $payment = CollectionPayment::create([
            'society_id' => $society?->id,
            'receipt_number' => $this->nextReceiptNumber($society),
            'member_id' => $data['member_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'maintenance_bill_id' => $data['maintenance_bill_id'] ?? null,
            'member_name' => $data['member_name'] ?? null,
            'flat_number' => $data['flat_number'] ?? null,
            'unit_label' => $data['unit_label'] ?? null,
            'bill_type' => $data['bill_type'],
            'bill_period' => $data['bill_period'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'receipt_date' => Carbon::parse($data['receipt_date']),
            'total_due' => $totalDue,
            'paid_amount' => $paid,
            'discount' => $discount,
            'fine_penalty' => $fine,
            'balance_due' => $balance,
            'payment_mode' => $data['payment_mode'],
            'reference_no' => $data['reference_no'] ?? null,
            'transaction_utr' => $data['transaction_utr'] ?? null,
            'collected_by' => auth()->user()->name ?? 'Neha Patil',
            'status' => $status,
            'is_online' => in_array($data['payment_mode'], ['upi', 'card', 'net_banking'], true),
            'notes' => $data['notes'] ?? null,
            'attachment_path' => $attachmentPath,
        ]);

        $this->allocateToBill($payment);

        if ($request->boolean('print')) {
            return redirect()->route('society.collections.receipts.show', ['payment' => $payment, 'print' => 1])
                ->with('success', "Payment {$payment->receipt_number} recorded successfully.");
        }

        return redirect()->route('society.collections.index')
            ->with('success', "Payment {$payment->receipt_number} recorded successfully.");
    }

    public function pendingDues(Request $request): View
    {
        $society = Society::first();

        $rows = $this->pendingDuesRows();
        $bucket = $request->string('bucket')->toString() ?: 'all';

        $filtered = collect($rows)
            ->when($bucket !== 'all', fn ($items) => $items->filter(fn ($r) => $r['bucket'] === $bucket))
            ->values();

        return view('society.collections.pending-dues', [
            'society' => $society,
            'rows' => $filtered,
            'bucket' => $bucket,
            'counts' => $this->pendingDuesCounts(),
            'kpis' => $this->pendingDuesKpis(),
            'aging' => $this->duesAgingDonut(),
        ]);
    }

    private function nextReceiptNumber(?Society $society): string
    {
        $series = NumberingSeries::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('document_type', 'receipt')
            ->first();

        if ($series) {
            return $series->generateNext();
        }

        $last = CollectionPayment::max('id') ?? 0;

        return 'RCPT-'.now()->format('Y').'-'.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Apply the payment to its linked maintenance bill (updates collected/outstanding/status).
     */
    private function allocateToBill(CollectionPayment $payment): void
    {
        if (! $payment->maintenance_bill_id) {
            return;
        }

        $bill = MaintenanceBill::find($payment->maintenance_bill_id);

        if (! $bill) {
            return;
        }

        $collected = (float) $bill->collected_amount + (float) $payment->paid_amount;
        $outstanding = max(0, (float) $bill->total_amount - $collected);

        $bill->update([
            'collected_amount' => $collected,
            'outstanding_amount' => $outstanding,
            'status' => $outstanding <= 0 ? 'paid' : ($collected > 0 ? 'partial' : $bill->status),
        ]);
    }

    /**
     * KPI figures mirror the reference design (Payment Collection.png §3.2).
     *
     * @return array<string, mixed>
     */
    private function kpis(): array
    {
        return [
            'month_collected' => 124850,
            'year_collected' => 1548600,
            'pending' => 326450,
            'pending_sub' => '26 Units / 32 Members',
            'overdue' => 112300,
            'overdue_sub' => '18 Units / 21 Members',
        ];
    }

    /**
     * Collection Overview donut segments + legend (design §3.6).
     *
     * @return array<string, mixed>
     */
    private function overviewDonut(): array
    {
        return [
            'segments' => [
                ['label' => 'Collected', 'value' => 15.48, 'pct' => '72.5%', 'amount' => '&#8377; 15.48L', 'color' => '#16a34a'],
                ['label' => 'Pending', 'value' => 3.26, 'pct' => '15.3%', 'amount' => '&#8377; 3.26L', 'color' => '#f59e0b'],
                ['label' => 'Overdue', 'value' => 1.12, 'pct' => '5.2%', 'amount' => '&#8377; 1.12L', 'color' => '#dc2626'],
                ['label' => 'Refunded', 'value' => 0.48, 'pct' => '2.0%', 'amount' => '&#8377; 0.48L', 'color' => '#7c3aed'],
            ],
            'center_value' => '&#8377; 15.48L',
            'center_label' => 'This Year',
            'total' => '&#8377; 20.34L',
        ];
    }

    /**
     * Recent Transactions rail (design §3.6).
     *
     * @return Collection<int, CollectionPayment>
     */
    private function recentTransactions(?Society $society)
    {
        return CollectionPayment::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('paid_amount', '>', 0)
            ->orderByDesc('receipt_date')
            ->take(3)
            ->get();
    }

    /**
     * KPI figures for Pending Dues (design §6.1).
     *
     * @return array<string, mixed>
     */
    private function pendingDuesKpis(): array
    {
        return [
            'outstanding' => 326450,
            'outstanding_sub' => '21 Units / 18 Members',
            'due_month' => 82450,
            'due_month_sub' => '8 Units / 7 Members',
            'overdue' => 244000,
            'overdue_sub' => '17 Units / 14 Members',
            'avg_days' => '45 Days',
            'avg_days_sub' => 'As on 31 May 2024',
        ];
    }

    /**
     * Dues Aging donut segments (design §6.5).
     *
     * @return array<string, mixed>
     */
    private function duesAgingDonut(): array
    {
        return [
            'segments' => [
                ['label' => '0 - 30 Days', 'value' => 82450, 'pct' => '25.2%', 'amount' => '&#8377; 82,450', 'color' => '#16a34a'],
                ['label' => '31 - 60 Days', 'value' => 74350, 'pct' => '22.8%', 'amount' => '&#8377; 74,350', 'color' => '#f59e0b'],
                ['label' => '61 - 90 Days', 'value' => 45650, 'pct' => '14.0%', 'amount' => '&#8377; 45,650', 'color' => '#ea580c'],
                ['label' => '90+ Days', 'value' => 123900, 'pct' => '37.9%', 'amount' => '&#8377; 1,23,900', 'color' => '#dc2626'],
            ],
            'center_value' => '&#8377; 3.26L',
            'center_label' => 'Total Dues',
        ];
    }

    /**
     * Tab counts for the aging pills (design §6.3).
     *
     * @return array<string, int>
     */
    private function pendingDuesCounts(): array
    {
        return [
            'all' => 21,
            '0-30' => 5,
            '31-60' => 6,
            '61-90' => 4,
            '90+' => 6,
        ];
    }

    /**
     * The exact 8 pending-dues rows shown on page 1 (design §6.4).
     *
     * @return array<int, array<string, mixed>>
     */
    private function pendingDuesRows(): array
    {
        // [name, flat, wing, period, due_date, total, paid, balance, days, bucket]
        $data = [
            ['Rahul Sharma', 'A-101', 'Wing A', 'May 2024', '31 May 2024', 2850.00, 0.00, 2850.00, 0, '0-30'],
            ['Priya Sharma', 'A-102', 'Wing A', 'May 2024', '31 May 2024', 2850.00, 1000.00, 1850.00, 0, '0-30'],
            ['Amit Patel', 'A-103', 'Wing A', 'May 2024', '31 May 2024', 2850.00, 0.00, 2850.00, 0, '0-30'],
            ['Neha Verma', 'A-104', 'Wing A', 'May 2024', '31 May 2024', 2850.00, 0.00, 2850.00, 0, '0-30'],
            ['Vikram Singh', 'A-201', 'Wing A', 'May 2024', '25 May 2024', 3150.00, 0.00, 3150.00, 6, '0-30'],
            ['Sneha Iyer', 'A-202', 'Wing A', 'May 2024', '20 May 2024', 2850.00, 0.00, 2850.00, 11, '0-30'],
            ['Meena Patel', 'A-203', 'Wing A', 'May 2024', '18 May 2024', 2850.00, 1500.00, 1350.00, 13, '0-30'],
            ['Anjali Singh', 'B-101', 'Wing B', 'May 2024', '15 May 2024', 2850.00, 0.00, 2850.00, 16, '0-30'],
        ];

        return array_map(fn ($r) => [
            'member_name' => $r[0],
            'flat_number' => $r[1],
            'wing' => $r[2],
            'bill_period' => $r[3],
            'due_date' => $r[4],
            'total_due' => $r[5],
            'paid' => $r[6],
            'balance' => $r[7],
            'days_overdue' => $r[8],
            'bucket' => $r[9],
        ], $data);
    }
}
