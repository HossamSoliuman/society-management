<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\PaymentGatewayOrder;
use App\Models\Society;
use App\Models\User;
use App\Notifications\PaymentReceiptIssued;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Online payments: create a gateway order for a bill (or a member's total
 * dues), then turn the provider's webhook into a CollectionPayment that is
 * allocated to bills and receipted.
 */
class OnlinePaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly PaymentAllocationService $allocations,
    ) {}

    /**
     * Create a local + provider order for one bill (full outstanding by default).
     */
    public function createOrderForBill(MaintenanceBill $bill, ?User $createdBy = null, ?float $amount = null): PaymentGatewayOrder
    {
        $amount = round($amount ?? (float) $bill->outstanding_amount, 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('This bill has nothing outstanding.');
        }

        return $this->createOrder($bill->society, [
            'member_id' => $bill->member_id,
            'unit_id' => $bill->unit_id,
            'maintenance_bill_id' => $bill->id,
            'amount' => $amount,
        ], $createdBy);
    }

    /**
     * Create an order for everything a member owes (allocated oldest-first on payment).
     */
    public function createOrderForMember(Member $member, ?User $createdBy = null, ?float $amount = null): PaymentGatewayOrder
    {
        $society = $member->society;
        $unit = $member->units()->first();
        $amount = round($amount ?? $this->allocations->outstandingFor($society, null, $member, $unit), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('There are no dues to pay.');
        }

        return $this->createOrder($society, [
            'member_id' => $member->id,
            'unit_id' => $unit?->id,
            'amount' => $amount,
        ], $createdBy);
    }

    /**
     * Verify + parse a webhook, then record the payment exactly once.
     * Returns the order (null when the request could not be matched).
     */
    public function handleWebhook(Request $request): ?PaymentGatewayOrder
    {
        if (! $this->gateway->verifyWebhook($request)) {
            abort(401, 'Invalid webhook signature.');
        }

        $event = $this->gateway->parseWebhook($request);
        if (! $event) {
            return null;
        }

        $order = PaymentGatewayOrder::query()->where('provider_order_id', $event['order_id'])->first();
        if (! $order) {
            return null;
        }

        return DB::transaction(function () use ($order, $event, $request): PaymentGatewayOrder {
            $order = PaymentGatewayOrder::query()->lockForUpdate()->find($order->id);

            if ($order->isPaid()) {
                return $order; // idempotent: provider retries must not double-post
            }

            if ($event['status'] !== 'paid') {
                $order->forceFill([
                    'status' => $event['status'],
                    'provider_payment_id' => $event['payment_id'],
                    'payload' => $request->all(),
                ])->save();

                return $order;
            }

            $paid = $event['amount'] > 0 ? $event['amount'] : (float) $order->amount;
            $payment = $this->allocations->record($order->society, [
                'member_id' => $order->member_id,
                'unit_id' => $order->unit_id,
                'maintenance_bill_id' => $order->maintenance_bill_id,
                'bill_type' => $order->bill?->billing_type ?? 'Maintenance',
                'receipt_date' => now(),
                'paid_amount' => $paid,
                'payment_mode' => $this->mapMethod($event['method']),
                'transaction_utr' => $event['payment_id'],
                'reference_no' => $order->provider_order_id,
                'is_online' => true,
                'notes' => 'Online payment via '.$this->gateway->name(),
            ], 'Online');

            $order->forceFill([
                'status' => 'paid',
                'provider_payment_id' => $event['payment_id'],
                'payment_method' => $event['method'],
                'collection_payment_id' => $payment->id,
                'payload' => $request->all(),
                'paid_at' => now(),
            ])->save();

            $this->emailReceipt($payment);

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOrder(Society $society, array $attributes, ?User $createdBy): PaymentGatewayOrder
    {
        $order = new PaymentGatewayOrder($attributes + [
            'society_id' => $society->id,
            'created_by' => $createdBy?->id,
            'provider' => $this->gateway->name(),
            'provider_order_id' => 'pending-'.uniqid(),
            'currency' => 'INR',
            'status' => 'created',
        ]);
        $order->save();

        $remote = $this->gateway->createOrder($order);
        $order->forceFill([
            'provider_order_id' => $remote['order_id'],
            'payload' => ['checkout' => $remote['checkout']],
        ])->save();

        return $order;
    }

    private function emailReceipt(CollectionPayment $payment): void
    {
        $email = $payment->member_email ?: $payment->member?->email;
        if ($email) {
            Notification::route('mail', $email)->notify(new PaymentReceiptIssued($payment));
        }
    }

    private function mapMethod(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'card', 'credit_card', 'debit_card' => 'card',
            'netbanking', 'net_banking' => 'net_banking',
            'upi', 'wallet' => 'upi',
            default => 'other',
        };
    }
}
