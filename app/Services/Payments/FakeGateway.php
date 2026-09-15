<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\PaymentGatewayOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Local / test driver: orders succeed instantly and the webhook accepts a
 * plain JSON body {order_id, payment_id, status, amount, method}.
 */
class FakeGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function createOrder(PaymentGatewayOrder $order): array
    {
        $orderId = 'fake_order_'.Str::lower(Str::random(14));

        return [
            'order_id' => $orderId,
            'checkout' => [
                'provider' => 'fake',
                'order_id' => $orderId,
                'amount' => (float) $order->amount,
                'currency' => $order->currency,
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }

    public function parseWebhook(Request $request): ?array
    {
        if (! $request->filled('order_id')) {
            return null;
        }

        return [
            'order_id' => (string) $request->input('order_id'),
            'payment_id' => $request->input('payment_id', 'fake_pay_'.Str::lower(Str::random(10))),
            'status' => (string) $request->input('status', 'paid'),
            'amount' => (float) $request->input('amount', 0),
            'method' => $request->input('method', 'upi'),
        ];
    }
}
