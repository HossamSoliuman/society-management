<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Society;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function overview()
    {
        $totalRevenue = Invoice::sum('total_amount');
        $totalCollected = Payment::where('status', 'success')->sum('amount');
        $totalOutstanding = Invoice::whereIn('status', ['pending', 'overdue'])->sum('outstanding_amount');
        $totalOverdue = Invoice::where('status', 'overdue')->sum('outstanding_amount');

        $monthlyRevenue = [];
        $monthlyOutstanding = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $monthlyRevenue[] = Payment::whereDate('payment_date', $date)->sum('amount');
            $monthlyOutstanding[] = Invoice::whereDate('due_date', $date)->sum('outstanding_amount');
        }

        $revenueByCategory = [
            ['name' => 'Maintenance Charges', 'amount' => 220000, 'percentage' => 63.6],
            ['name' => 'Parking Charges', 'amount' => 45000, 'percentage' => 13.0],
            ['name' => 'Sinking Fund', 'amount' => 30000, 'percentage' => 8.7],
            ['name' => 'Water Charges', 'amount' => 20000, 'percentage' => 5.8],
            ['name' => 'Other Charges', 'amount' => 30678, 'percentage' => 8.9],
        ];

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

    public function invoices()
    {
        $invoices = Invoice::with('society')->latest()->paginate(10);
        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $paidAmount = Invoice::where('status', 'paid')->sum('total_amount');
        $pendingAmount = Invoice::whereIn('status', ['pending', 'overdue'])->sum('outstanding_amount');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('outstanding_amount');

        return view('superadmin.billing.invoices', compact(
            'invoices', 'totalInvoices', 'paidInvoices', 'pendingInvoices', 'overdueInvoices',
            'paidAmount', 'pendingAmount', 'overdueAmount'
        ));
    }

    public function payments()
    {
        $payments = Payment::with('society')->latest()->paginate(10);
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

    public function receipts()
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

    public function outstanding()
    {
        $invoices = Invoice::with('society')->whereIn('status', ['pending', 'overdue'])->latest()->paginate(10);
        $totalOutstandingAmount = Invoice::whereIn('status', ['pending', 'overdue'])->sum('outstanding_amount');
        $totalOutstandingInvoices = Invoice::whereIn('status', ['pending', 'overdue'])->count();
        $overdueAmount = Invoice::where('status', 'overdue')->sum('outstanding_amount');
        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        return view('superadmin.billing.outstanding', compact(
            'invoices', 'totalOutstandingAmount', 'totalOutstandingInvoices',
            'overdueAmount', 'overdueInvoices'
        ));
    }

    public function overdue()
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

    public function refunds()
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
}
