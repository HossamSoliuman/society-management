<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMode;
use App\Models\Refund;
use App\Models\Society;
use App\Models\Subscription;
use App\Services\PlatformBillingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class BillingController extends Controller
{
    public function __construct(private readonly PlatformBillingService $billing) {}

    public function overview(): View
    {
        $totalRevenue = Invoice::sum('total_amount');
        $totalCollected = Payment::where('status', 'success')->sum('amount');
        $totalOutstanding = Invoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->sum('outstanding_amount');
        $totalOverdue = Invoice::where('status', 'overdue')->sum('outstanding_amount');

        $monthlyRevenue = [];
        $monthlyOutstanding = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $monthlyRevenue[] = Payment::whereDate('payment_date', $date)->where('status', 'success')->sum('amount');
            $monthlyOutstanding[] = Invoice::whereDate('due_date', $date)->sum('outstanding_amount');
        }

        $revenueByCategory = $this->billing->revenueByCategory();

        $collectionStatus = [
            'collected' => $totalCollected,
            'outstanding' => $totalOutstanding,
            'overdue' => $totalOverdue,
            'collected_percent' => $totalRevenue > 0 ? round(($totalCollected / $totalRevenue) * 100) : 0,
        ];

        $recentInvoices = Invoice::with('society')->latest()->take(5)->get();

        return view('superadmin.billing.overview', compact(
            'totalRevenue', 'totalCollected', 'totalOutstanding', 'totalOverdue',
            'monthlyRevenue', 'monthlyOutstanding', 'revenueByCategory', 'collectionStatus', 'recentInvoices'
        ));
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

    public function payments(): View
    {
        $payments = Payment::with(['society', 'invoice'])->latest()->paginate(10);
        $totalCollections = Payment::where('status', 'success')->sum('amount');
        $totalPaymentsReceived = Payment::where('status', 'success')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $failedPayments = Payment::where('status', 'failed')->count();
        $pendingAmount = Payment::where('status', 'pending')->sum('amount');
        $failedAmount = Payment::where('status', 'failed')->sum('amount');

        return view('superadmin.billing.payments', compact(
            'payments', 'totalCollections', 'totalPaymentsReceived',
            'pendingPayments', 'failedPayments', 'pendingAmount', 'failedAmount'
        ));
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
}
