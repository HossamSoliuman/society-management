<?php

namespace App\Http\Controllers;

use App\Services\OnlinePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provider callbacks for online payments. Unauthenticated, CSRF-exempt and
 * signature-verified by the gateway driver.
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, OnlinePaymentService $payments): JsonResponse
    {
        $order = $payments->handleWebhook($request);

        return response()->json([
            'received' => true,
            'order' => $order?->provider_order_id,
            'status' => $order?->status,
        ]);
    }
}
