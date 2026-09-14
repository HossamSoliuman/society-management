<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\View\View;

/**
 * Read-only "My Subscription" page: current plan, term, access end date,
 * platform invoices raised against the society and their payment status.
 */
class SubscriptionController extends Controller
{
    public function index(): View
    {
        $society = $this->currentSociety();

        $current = Subscription::query()
            ->with('plan')
            ->where('society_id', $society->id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first();

        $history = Subscription::query()
            ->with(['plan', 'renewedFrom'])
            ->where('society_id', $society->id)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $invoices = Invoice::query()
            ->with('payments')
            ->where('society_id', $society->id)
            ->where('invoice_type', 'Subscription')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(10);

        $payments = Payment::query()
            ->where('society_id', $society->id)
            ->whereIn('invoice_id', Invoice::query()->where('society_id', $society->id)->where('invoice_type', 'Subscription')->select('id'))
            ->where('status', 'success')
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        return view('society.subscription.index', [
            'society' => $society,
            'current' => $current,
            'history' => $history,
            'invoices' => $invoices,
            'payments' => $payments,
            'outstanding' => (float) Invoice::query()
                ->where('society_id', $society->id)
                ->where('invoice_type', 'Subscription')
                ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
                ->sum('outstanding_amount'),
        ]);
    }
}
