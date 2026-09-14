<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PrefixSetting;
use App\Models\Refund;
use App\Models\Society;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Platform-level billing: invoices the SaaS raises against societies for their
 * subscription, payments recorded against those invoices, and refunds.
 */
class PlatformBillingService
{
    /** Days after invoice_date the platform invoice falls due by default. */
    public const DEFAULT_DUE_DAYS = 15;

    /**
     * Raise an invoice for a subscription term. Amount defaults to the
     * subscription amount, or monthly_cost_per_flat × total units when set.
     *
     * @param  array<string, mixed>  $overrides  amount, tax_amount, invoice_date, due_date, notes, category
     */
    public function createInvoiceForSubscription(Subscription $subscription, array $overrides = []): Invoice
    {
        $society = $subscription->society;
        $amount = (float) ($overrides['amount'] ?? $this->defaultAmount($subscription, $society));
        $tax = (float) ($overrides['tax_amount'] ?? 0);
        $invoiceDate = isset($overrides['invoice_date']) ? Carbon::parse($overrides['invoice_date']) : Carbon::today();
        $dueDate = isset($overrides['due_date']) ? Carbon::parse($overrides['due_date']) : $invoiceDate->copy()->addDays(self::DEFAULT_DUE_DAYS);

        return DB::transaction(function () use ($subscription, $society, $amount, $tax, $invoiceDate, $dueDate, $overrides): Invoice {
            return Invoice::create([
                'invoice_number' => PrefixSetting::generate('Invoice'),
                'society_id' => $society->id,
                'subscription_id' => $subscription->id,
                'member_name' => $society->chairman_name ?: $society->name,
                'flat_number' => null,
                'building_name' => $society->name,
                'invoice_type' => 'Subscription',
                'category' => $overrides['category'] ?? ($subscription->plan?->name ?? 'Subscription'),
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'amount' => $amount,
                'tax_amount' => $tax,
                'total_amount' => $amount + $tax,
                'paid_amount' => 0,
                'outstanding_amount' => $amount + $tax,
                'status' => 'pending',
                'notes' => $overrides['notes'] ?? "{$subscription->plan?->name} · {$subscription->start_date->format('d M Y')} – {$subscription->end_date->format('d M Y')}",
            ]);
        });
    }

    /**
     * Record a payment against an invoice and roll the invoice totals forward.
     *
     * @param  array<string, mixed>  $data  amount, payment_method, transaction_id, payment_date, notes
     */
    public function recordPayment(Invoice $invoice, array $data, ?User $recordedBy = null): Payment
    {
        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }
        if ($amount > (float) $invoice->outstanding_amount + 0.005) {
            throw new InvalidArgumentException('Payment exceeds the invoice outstanding amount.');
        }

        return DB::transaction(function () use ($invoice, $data, $amount, $recordedBy): Payment {
            $payment = Payment::create([
                'receipt_number' => PrefixSetting::generate('Receipt'),
                'invoice_id' => $invoice->id,
                'society_id' => $invoice->society_id,
                'member_name' => $invoice->member_name,
                'flat_number' => $invoice->flat_number,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_date' => $data['payment_date'] ?? Carbon::today(),
                'status' => 'success',
                'recorded_by' => $recordedBy?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recalculateInvoice($invoice);

            return $payment;
        });
    }

    /**
     * Issue a refund against a successful payment.
     *
     * @param  array<string, mixed>  $data  amount, refund_method, refund_date, reason, status
     */
    public function issueRefund(Payment $payment, array $data): Refund
    {
        $amount = round((float) $data['amount'], 2);
        $alreadyRefunded = (float) Refund::query()->where('payment_id', $payment->id)->where('status', '!=', 'failed')->sum('amount');

        if ($amount <= 0 || $amount > (float) $payment->amount - $alreadyRefunded + 0.005) {
            throw new InvalidArgumentException('Refund amount exceeds the refundable balance of this payment.');
        }

        return DB::transaction(function () use ($payment, $data, $amount): Refund {
            $refund = Refund::create([
                'refund_number' => PrefixSetting::generate('Refund'),
                'payment_id' => $payment->id,
                'society_id' => $payment->society_id,
                'member_name' => $payment->member_name,
                'flat_number' => $payment->flat_number,
                'amount' => $amount,
                'refund_method' => $data['refund_method'],
                'refund_date' => $data['refund_date'] ?? Carbon::today(),
                'status' => $data['status'] ?? 'completed',
                'reason' => $data['reason'] ?? null,
            ]);

            if ($refund->status === 'completed' && $payment->invoice) {
                $this->recalculateInvoice($payment->invoice);
            }

            return $refund;
        });
    }

    /**
     * Flip pending invoices past their due date to overdue. Returns rows changed.
     */
    public function markOverdue(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::today();

        return Invoice::query()
            ->whereIn('status', ['pending', 'partially_paid'])
            ->whereDate('due_date', '<', $asOf->toDateString())
            ->where('outstanding_amount', '>', 0)
            ->update(['status' => 'overdue', 'updated_at' => now()]);
    }

    /**
     * Recompute paid / outstanding / status from successful payments minus
     * completed refunds.
     */
    public function recalculateInvoice(Invoice $invoice): Invoice
    {
        $paid = (float) Payment::query()->where('invoice_id', $invoice->id)->where('status', 'success')->sum('amount');
        $refunded = (float) Refund::query()
            ->whereIn('payment_id', Payment::query()->where('invoice_id', $invoice->id)->select('id'))
            ->where('status', 'completed')
            ->sum('amount');

        $net = max(0, $paid - $refunded);
        $outstanding = max(0, round((float) $invoice->total_amount - $net, 2));

        $status = match (true) {
            $invoice->status === 'cancelled' => 'cancelled',
            $outstanding <= 0 => 'paid',
            $net > 0 => 'partially_paid',
            $invoice->due_date->lt(Carbon::today()) => 'overdue',
            default => 'pending',
        };

        $invoice->forceFill([
            'paid_amount' => $net,
            'outstanding_amount' => $outstanding,
            'status' => $status,
        ])->save();

        return $invoice;
    }

    /**
     * Revenue collected per subscription plan, from successful payments joined
     * through invoices → subscriptions.
     *
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    public function revenueByPlan(): array
    {
        $rows = Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('subscriptions', 'subscriptions.id', '=', 'invoices.subscription_id')
            ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->where('payments.status', 'success')
            ->whereNull('payments.deleted_at')
            ->selectRaw("COALESCE(subscription_plans.name, 'Unassigned') as name, SUM(payments.amount) as amount")
            ->groupBy('subscription_plans.name')
            ->orderByDesc('amount')
            ->get();

        return $this->withPercentages($rows->map(fn ($r) => ['name' => $r->name, 'amount' => (float) $r->amount])->all());
    }

    /**
     * Revenue collected per invoice category.
     *
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    public function revenueByCategory(): array
    {
        $rows = Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('payments.status', 'success')
            ->whereNull('payments.deleted_at')
            ->selectRaw('COALESCE(invoices.category, invoices.invoice_type) as name, SUM(payments.amount) as amount')
            ->groupByRaw('COALESCE(invoices.category, invoices.invoice_type)')
            ->orderByDesc('amount')
            ->get();

        return $this->withPercentages($rows->map(fn ($r) => ['name' => $r->name, 'amount' => (float) $r->amount])->all());
    }

    private function defaultAmount(Subscription $subscription, Society $society): float
    {
        if ((float) $subscription->monthly_cost_per_flat > 0) {
            $units = (int) $society->flats_count + (int) $society->shops_count + (int) $society->offices_count;
            $months = max(1, (int) $subscription->start_date->diffInMonths($subscription->end_date->copy()->addDay()));

            return round((float) $subscription->monthly_cost_per_flat * $units * $months, 2);
        }

        return (float) ($subscription->amount ?: $subscription->plan?->amount ?? 0);
    }

    /**
     * @param  array<int, array{name: string, amount: float}>  $rows
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    private function withPercentages(array $rows): array
    {
        $total = array_sum(array_column($rows, 'amount'));

        return array_map(fn (array $row) => $row + [
            'percentage' => $total > 0 ? round($row['amount'] / $total * 100, 1) : 0.0,
        ], $rows);
    }
}
