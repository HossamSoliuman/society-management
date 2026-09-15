<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\PaymentGatewayOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Razorpay Orders API + webhook (payment.captured / order.paid).
 * Config: services.razorpay.key / secret / webhook_secret.
 */
class RazorpayGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
        private readonly ?string $webhookSecret,
    ) {}

    public function name(): string
    {
        return 'razorpay';
    }

    public function createOrder(PaymentGatewayOrder $order): array
    {
        $response = Http::withBasicAuth($this->key, $this->secret)
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round((float) $order->amount * 100),
                'currency' => $order->currency,
                'receipt' => 'order_'.$order->id,
                'notes' => [
                    'society_id' => $order->society_id,
                    'bill_id' => $order->maintenance_bill_id,
                    'member_id' => $order->member_id,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Razorpay order creation failed: '.$response->body());
        }

        $orderId = (string) $response->json('id');

        return [
            'order_id' => $orderId,
            'checkout' => [
                'provider' => 'razorpay',
                'key' => $this->key,
                'order_id' => $orderId,
                'amount' => (int) round((float) $order->amount * 100),
                'currency' => $order->currency,
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        if (! $this->webhookSecret) {
            return false;
        }

        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

        return $signature !== '' && hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): ?array
    {
        $payload = $request->json()->all();
        $entity = $payload['payload']['payment']['entity'] ?? null;
        if (! $entity || empty($entity['order_id'])) {
            return null;
        }

        $event = (string) ($payload['event'] ?? '');
        $status = match (true) {
            in_array($event, ['payment.captured', 'order.paid'], true) => 'paid',
            $event === 'payment.failed' => 'failed',
            default => (string) ($entity['status'] ?? 'created'),
        };

        return [
            'order_id' => (string) $entity['order_id'],
            'payment_id' => $entity['id'] ?? null,
            'status' => $status === 'captured' ? 'paid' : $status,
            'amount' => round(((int) ($entity['amount'] ?? 0)) / 100, 2),
            'method' => $entity['method'] ?? null,
        ];
    }
}
