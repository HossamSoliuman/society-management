<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\PlatformBillingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo platform billing: monthly subscription instalments for the last
 * twelve months per subscription, raised and paid through
 * PlatformBillingService so totals, statuses and receipts stay consistent.
 * The two most recent instalments of every third society are left unpaid.
 */
class PlatformBillingSeeder extends Seeder
{
    /** Yearly figure used for plans priced "on request" (amount 0). */
    private const CUSTOM_PLAN_YEARLY = 24999;

    /** @var array<int, string> */
    private const METHODS = ['Bank Transfer', 'UPI', 'Net Banking', 'Cheque', 'Credit Card'];

    public function run(PlatformBillingService $billing): void
    {
        $subscriptions = Subscription::query()
            ->with(['society', 'plan'])
            ->whereHas('society')
            ->whereDoesntHave('society.invoices', fn ($q) => $q->whereNotNull('subscription_id'))
            ->orderBy('society_id')
            ->get();

        foreach ($subscriptions as $index => $subscription) {
            $yearly = (float) $subscription->amount ?: (float) ($subscription->plan?->amount ?: self::CUSTOM_PLAN_YEARLY);
            $instalment = round($yearly / 12, 2);
            $leaveRecentUnpaid = $index % 3 === 2;

            for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
                $invoiceDate = Carbon::today()->startOfMonth()->subMonthsNoOverflow($monthsAgo);

                $invoice = $billing->createInvoiceForSubscription($subscription, [
                    'amount' => $instalment,
                    'tax_amount' => round($instalment * 0.18, 2),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $invoiceDate->copy()->addDays(15),
                    'notes' => "{$subscription->plan?->name} · instalment for {$invoiceDate->format('M Y')}",
                ]);

                if ($leaveRecentUnpaid && $monthsAgo <= 1) {
                    continue;
                }

                $paidOn = $invoiceDate->copy()->addDays(3 + ($index + $monthsAgo) % 9);
                if ($paidOn->isFuture()) {
                    continue;
                }

                $billing->recordPayment($invoice, [
                    'amount' => $invoice->outstanding_amount,
                    'payment_method' => self::METHODS[($index + $monthsAgo) % count(self::METHODS)],
                    'transaction_id' => 'TXN'.$invoiceDate->format('ym').str_pad((string) $subscription->society_id, 3, '0', STR_PAD_LEFT),
                    'payment_date' => $paidOn,
                ]);
            }
        }

        $billing->markOverdue();
    }
}
