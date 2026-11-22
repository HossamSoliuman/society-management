<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMode;
use App\Models\Refund;
use App\Models\Society;
use App\Models\Subscription;
use App\Notifications\PlatformInvoiceReminder;
use App\Services\PlatformBillingService;
use App\Support\Period;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class BillingController extends Controller
{
    public function __construct(private readonly PlatformBillingService $billing) {}

    public function overview(Request $request): View
    {
        $period = Period::fromKey($request->query('range'));
        $outstanding = (float) Invoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->sum('outstanding_amount');
        $overdue = (float) Invoice::where('status', 'overdue')->sum('outstanding_amount');
        $collected = (float) Payment::where('status', 'success')->sum('amount');
        $statusTotal = $collected + $outstanding;

        return view('superadmin.billing.overview', [
            'd' => $this->buildOverview($period),
            'range' => $period->key,
            'rangeOptions' => Period::options(),
            'collectionStatus' => [
                'collected' => $collected,
                'outstanding' => $outstanding - $overdue,
                'overdue' => $overdue,
                'collected_percent' => $statusTotal > 0 ? (int) round($collected / $statusTotal * 100) : 0,
                'outstanding_percent' => $statusTotal > 0 ? (int) round(($outstanding - $overdue) / $statusTotal * 100) : 0,
                'overdue_percent' => $statusTotal > 0 ? (int) round($overdue / $statusTotal * 100) : 0,
            ],
            'recentInvoices' => Invoice::with('society')->latest()->take(5)->get(),
        ]);
    }

    /**
     * AJAX endpoint: the period-dependent overview cards as HTML fragments.
     */
    public function overviewData(Request $request): JsonResponse
    {
        $period = Period::fromKey($request->query('range'));
        $d = $this->buildOverview($period);

        return response()->json([
            'range' => $period->key,
            'rangeLabel' => $period->label(),
            'fragments' => [
                'stats' => view('superadmin.billing.partials.overview-stats', ['d' => $d])->render(),
                'revenue' => view('superadmin.billing.partials.overview-revenue', ['d' => $d])->render(),
                'categories' => view('superadmin.billing.partials.overview-categories', ['d' => $d])->render(),
            ],
        ]);
    }

    public function invoices(Request $request): View
    {
        $invoices = Invoice::with('society')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('society'), fn ($q) => $q->where('society_id', $request->integer('society')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('member_name', 'like', "%{$term}%")
                    ->orWhere('building_name', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalInvoices = Invoice::count();
        $totalAmount = Invoice::sum('total_amount');
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $pendingInvoices = Invoice::whereIn('status', ['pending', 'partially_paid'])->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $paidAmount = Payment::where('status', 'success')->sum('amount');
        $pendingAmount = Invoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->sum('outstanding_amount');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('outstanding_amount');
        $invoiceTypes = Invoice::query()
            ->selectRaw('COALESCE(category, invoice_type) as name, COUNT(*) as c')
            ->groupByRaw('COALESCE(category, invoice_type)')
            ->orderByDesc('c')
            ->limit(6)
            ->pluck('c', 'name');
        $societies = Society::orderBy('name')->get(['id', 'name']);

        return view('superadmin.billing.invoices', compact(
            'invoices', 'totalInvoices', 'totalAmount', 'paidInvoices', 'pendingInvoices', 'overdueInvoices',
            'paidAmount', 'pendingAmount', 'overdueAmount', 'invoiceTypes', 'societies'
        ));
    }

    public function createInvoice(Request $request): View
    {
        $subscriptions = Subscription::with(['society', 'plan'])
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')
            ->get();

        return view('superadmin.billing.invoice-create', [
            'subscriptions' => $subscriptions,
            'selected' => $request->integer('subscription') ?: null,
            'defaultDueDays' => PlatformBillingService::DEFAULT_DUE_DAYS,
        ]);
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $subscription = Subscription::with(['society', 'plan'])->findOrFail($validated['subscription_id']);
        unset($validated['subscription_id']);

        $invoice = $this->billing->createInvoiceForSubscription($subscription, array_filter($validated, fn ($v) => $v !== null && $v !== ''));

        return redirect()->route('superadmin.billing.invoices')
            ->with('success', "Invoice {$invoice->invoice_number} raised for {$subscription->society->name} (₹".number_format((float) $invoice->total_amount, 2).').');
    }

    public function payments(Request $request): View
    {
        $period = Period::fromKey($request->query('range'));

        return view('superadmin.billing.payments', [
            'payments' => Payment::with(['society', 'invoice'])->latest()->paginate(10),
            'd' => $this->buildPaymentSummary($period),
            'range' => $period->key,
            'rangeOptions' => Period::options(),
        ]);
    }

    /**
     * AJAX endpoint: the period-dependent payment cards as HTML fragments.
     */
    public function paymentsData(Request $request): JsonResponse
    {
        $period = Period::fromKey($request->query('range'));
        $d = $this->buildPaymentSummary($period);

        return response()->json([
            'range' => $period->key,
            'rangeLabel' => $period->label(),
            'fragments' => [
                'stats' => view('superadmin.billing.partials.payments-stats', ['d' => $d])->render(),
                'summary' => view('superadmin.billing.partials.payments-summary', ['d' => $d])->render(),
                'methods' => view('superadmin.billing.partials.payments-methods', ['d' => $d])->render(),
            ],
        ]);
    }

    public function createPayment(Request $request): View
    {
        $invoices = Invoice::with('society')
            ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
            ->where('outstanding_amount', '>', 0)
            ->orderBy('due_date')
            ->get();

        return view('superadmin.billing.payment-create', [
            'invoices' => $invoices,
            'selected' => $request->integer('invoice') ?: null,
            'paymentModes' => PaymentMode::where('status', 'active')->orderBy('name')->pluck('name'),
        ]);
    }

    public function recordPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        try {
            $payment = $this->billing->recordPayment($invoice, $validated, $request->user());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['amount' => $exception->getMessage()]);
        }

        return redirect()->route('superadmin.billing.payments')
            ->with('success', "Payment {$payment->receipt_number} recorded against {$invoice->invoice_number}; outstanding is now ₹".number_format((float) $invoice->fresh()->outstanding_amount, 2).'.');
    }

    public function receipts(): View
    {
        $payments = Payment::with(['society', 'invoice'])->where('status', 'success')->latest()->paginate(10);
        $totalCollections = Payment::where('status', 'success')->sum('amount');
        $receiptsIssued = Payment::where('status', 'success')->count();
        $pendingReceipts = Payment::where('status', 'pending')->count();
        $cancelledReceipts = Payment::where('status', 'failed')->count();

        return view('superadmin.billing.receipts', compact(
            'payments', 'totalCollections', 'receiptsIssued', 'pendingReceipts', 'cancelledReceipts'
        ));
    }

    public function outstanding(): View
    {
        $open = ['pending', 'partially_paid', 'overdue'];
        $invoices = Invoice::with('society')->whereIn('status', $open)->latest()->paginate(10);
        $totalOutstandingAmount = Invoice::whereIn('status', $open)->sum('outstanding_amount');
        $totalOutstandingInvoices = Invoice::whereIn('status', $open)->count();
        $overdueAmount = Invoice::where('status', 'overdue')->sum('outstanding_amount');
        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        return view('superadmin.billing.outstanding', compact(
            'invoices', 'totalOutstandingAmount', 'totalOutstandingInvoices',
            'overdueAmount', 'overdueInvoices'
        ));
    }

    public function overdue(): View
    {
        $invoices = Invoice::with('society')->where('status', 'overdue')->latest()->paginate(10);
        $totalOverdueAmount = Invoice::where('status', 'overdue')->sum('outstanding_amount');
        $totalOverdueInvoices = Invoice::where('status', 'overdue')->count();
        $overdue30Days = Invoice::where('status', 'overdue')->where('due_date', '<=', now()->subDays(30))->count();
        $overdue60Days = Invoice::where('status', 'overdue')->where('due_date', '<=', now()->subDays(60))->count();
        $overdue30Amount = Invoice::where('status', 'overdue')->where('due_date', '<=', now()->subDays(30))->sum('outstanding_amount');
        $overdue60Amount = Invoice::where('status', 'overdue')->where('due_date', '<=', now()->subDays(60))->sum('outstanding_amount');

        return view('superadmin.billing.overdue', compact(
            'invoices', 'totalOverdueAmount', 'totalOverdueInvoices',
            'overdue30Days', 'overdue60Days', 'overdue30Amount', 'overdue60Amount'
        ));
    }

    public function refunds(): View
    {
        $refunds = Refund::with(['society', 'payment'])->latest()->paginate(10);
        $totalRefundAmount = Refund::where('status', 'completed')->sum('amount');
        $totalRefunds = Refund::count();
        $pendingRefunds = Refund::where('status', 'pending')->count();
        $failedRefunds = Refund::where('status', 'failed')->count();
        $pendingRefundAmount = Refund::where('status', 'pending')->sum('amount');
        $failedRefundAmount = Refund::where('status', 'failed')->sum('amount');

        return view('superadmin.billing.refunds', compact(
            'refunds', 'totalRefundAmount', 'totalRefunds',
            'pendingRefunds', 'failedRefunds', 'pendingRefundAmount', 'failedRefundAmount'
        ));
    }

    public function createRefund(Request $request): View
    {
        $payments = Payment::with(['society', 'invoice'])
            ->where('status', 'success')
            ->latest('payment_date')
            ->get()
            ->filter(fn (Payment $payment) => $payment->refundableAmount() > 0)
            ->values();

        return view('superadmin.billing.refund-create', [
            'payments' => $payments,
            'selected' => $request->integer('payment') ?: null,
            'paymentModes' => PaymentMode::where('status', 'active')->orderBy('name')->pluck('name'),
        ]);
    }

    public function storeRefund(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|string|max:100',
            'refund_date' => 'required|date',
            'status' => 'required|in:completed,pending',
            'reason' => 'nullable|string|max:500',
        ]);

        $payment = Payment::with('invoice')->findOrFail($validated['payment_id']);

        try {
            $refund = $this->billing->issueRefund($payment, $validated);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['amount' => $exception->getMessage()]);
        }

        return redirect()->route('superadmin.billing.refunds')
            ->with('success', "Refund {$refund->refund_number} of ₹".number_format((float) $refund->amount, 2)." issued against {$payment->receipt_number}.");
    }

    public function showInvoice(Invoice $invoice): View
    {
        return view('superadmin.billing.invoice-show', [
            'invoice' => $invoice->load(['society', 'subscription.plan', 'payments']),
            'company' => CompanyProfile::first(),
        ]);
    }

    public function showReceipt(Payment $payment): View
    {
        return view('superadmin.billing.receipt-show', [
            'payment' => $payment->load(['society', 'invoice', 'refunds']),
            'company' => CompanyProfile::first(),
        ]);
    }

    /**
     * Email and notify society admins about open invoices: one invoice, or every
     * open (or only overdue) invoice when none is given.
     */
    public function remindInvoices(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'scope' => ['nullable', 'in:open,overdue'],
        ]);

        $invoices = Invoice::query()
            ->with('society.users.roles')
            ->where('outstanding_amount', '>', 0)
            ->when(! empty($data['invoice_id']), fn ($q) => $q->whereKey($data['invoice_id']))
            ->when(empty($data['invoice_id']), fn ($q) => $q->whereIn('status', ($data['scope'] ?? 'open') === 'overdue' ? ['overdue'] : ['pending', 'partially_paid', 'overdue']))
            ->get();

        $sent = 0;
        foreach ($invoices as $invoice) {
            $admins = $invoice->society?->users->filter(fn ($user) => $user->status === 'active' && $user->hasRole('society_admin')) ?? collect();
            foreach ($admins as $admin) {
                $admin->notify(new PlatformInvoiceReminder($invoice));
            }
            $sent += $admins->isNotEmpty() ? 1 : 0;
        }

        return back()->with($sent ? 'success' : 'error', $sent
            ? "Reminder sent for {$sent} invoice(s)."
            : 'No society admin to remind for the selected invoices.');
    }

    /**
     * Invoiced vs collected figures for a period, compared with the one before it.
     *
     * @return array<string, mixed>
     */
    private function buildOverview(Period $period): array
    {
        $previous = $period->previous();
        $invoiced = fn (Period $p): float => (float) $p->within(Invoice::query(), 'invoice_date')->sum('total_amount');
        $collected = fn (Period $p): float => (float) $p->within(Payment::where('status', 'success'), 'payment_date')->sum('amount');
        $change = fn (float $now, float $before): ?float => $before > 0 ? round(($now - $before) / $before * 100, 1) : null;

        $invoicedNow = $invoiced($period);
        $collectedNow = $collected($period);

        return [
            'period' => $period,
            'stats' => [
                'invoiced' => $invoicedNow,
                'invoiced_change' => $change($invoicedNow, $invoiced($previous)),
                'collected' => $collectedNow,
                'collected_change' => $change($collectedNow, $collected($previous)),
                'outstanding' => (float) Invoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->sum('outstanding_amount'),
                'outstanding_count' => Invoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->count(),
                'overdue' => (float) Invoice::where('status', 'overdue')->sum('outstanding_amount'),
                'overdue_count' => Invoice::where('status', 'overdue')->count(),
            ],
            'chart' => [
                'labels' => array_values($period->buckets()),
                'collected' => $period->series($period->within(Payment::where('status', 'success'), 'payment_date'), 'payment_date', 'amount'),
                'invoiced' => $period->series($period->within(Invoice::query(), 'invoice_date'), 'invoice_date', 'total_amount'),
            ],
            'revenueByCategory' => $this->billing->revenueByCategory($period),
        ];
    }

    /**
     * Payment totals by status and method for a period.
     *
     * @return array<string, mixed>
     */
    private function buildPaymentSummary(Period $period): array
    {
        $payments = fn (string $status, ?Period $window = null) => ($window ?? $period)->within(Payment::where('status', $status), 'payment_date');
        $successful = (float) $payments('success')->sum('amount');
        $previous = (float) $payments('success', $period->previous())->sum('amount');
        $refunded = (float) $period->within(Refund::where('status', 'completed'), 'refund_date')->sum('amount');

        return [
            'period' => $period,
            'successful' => $successful,
            'successful_change' => $previous > 0 ? round(($successful - $previous) / $previous * 100, 1) : null,
            'successful_count' => $payments('success')->count(),
            'pending' => (float) $payments('pending')->sum('amount'),
            'pending_count' => $payments('pending')->count(),
            'failed' => (float) $payments('failed')->sum('amount'),
            'failed_count' => $payments('failed')->count(),
            'refunded' => $refunded,
            'net' => $successful - $refunded,
            'methods' => $this->billing->revenueByMethod($period),
        ];
    }
}
