<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceBill;
use App\Models\PaymentGatewayOrder;
use App\Services\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Society-side "collect online": creates a gateway order for a bill and shows
 * the checkout page (the same page the member portal uses).
 */
class OnlinePaymentController extends Controller
{
    public function __construct(private readonly OnlinePaymentService $payments) {}

    public function createForBill(Request $request, MaintenanceBill $bill): RedirectResponse
    {
        try {
            $order = $this->payments->createOrderForBill($bill, $request->user());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['bill' => $exception->getMessage()]);
        }

        return redirect()->route('society.collections.online.checkout', $order);
    }

    public function checkout(PaymentGatewayOrder $order): View
    {
        $order->load(['bill', 'member', 'society', 'payment']);

        return view('society.collections.checkout', [
            'order' => $order,
            'checkout' => $order->payload['checkout'] ?? [],
            'returnUrl' => $order->maintenance_bill_id
                ? route('society.billing.bills.show', $order->maintenance_bill_id)
                : route('society.collections.index'),
            'webhookUrl' => route('webhooks.payments'),
        ]);
    }
}
