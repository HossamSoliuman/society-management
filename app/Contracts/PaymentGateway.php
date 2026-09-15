<?php

namespace App\Contracts;

use App\Models\PaymentGatewayOrder;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Short provider key stored on orders (e.g. "razorpay", "fake").
     */
    public function name(): string;

    /**
     * Create the provider-side order for a local order and return its id plus
     * whatever the checkout page needs (keys, amounts in minor units, ...).
     *
     * @return array{order_id: string, checkout: array<string, mixed>}
     */
    public function createOrder(PaymentGatewayOrder $order): array;

    /**
     * Validate the webhook signature / origin.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Normalise the webhook body.
     *
     * @return array{order_id: string, payment_id: ?string, status: string, amount: float, method: ?string}|null
     */
    public function parseWebhook(Request $request): ?array;
}
