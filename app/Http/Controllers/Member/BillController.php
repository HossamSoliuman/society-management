<?php

namespace App\Http\Controllers\Member;

use App\Models\MaintenanceBill;
use App\Models\PaymentGatewayOrder;
use App\Services\BillDocumentService;
use App\Services\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class BillController extends PortalController
{
    public function __construct(
        private readonly BillDocumentService $documents,
        private readonly OnlinePaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $member = $this->currentMember();

        $bills = $member->bills()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('bill_number', 'like', "%{$term}%")->orWhere('bill_month', 'like', "%{$term}%"));
            })
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $open = $member->bills()->whereIn('status', ['pending', 'partial', 'overdue']);

        return view('member.bills.index', [
            'member' => $member,
            'bills' => $bills,
            'stats' => [
                'outstanding' => (float) (clone $open)->sum('outstanding_amount'),
                'open' => (clone $open)->count(),
                'overdue' => (clone $open)->where('status', 'overdue')->count(),
                'paid' => $member->bills()->where('status', 'paid')->count(),
            ],
        ]);
    }

    public function show(MaintenanceBill $bill): View
    {
        $this->ownedByMember($bill->member_id);
        $bill->load(['items', 'payments']);

        return view('member.bills.show', [
            'design' => $this->documents->design($bill->society_id),
            'billModel' => $bill,
            'bill' => $this->documents->templateData($bill),
        ]);
    }

    public function pdf(MaintenanceBill $bill): Response
    {
        $this->ownedByMember($bill->member_id);

        return $this->documents->renderPdf($bill)->download($this->documents->fileName($bill));
    }

    public function pay(Request $request, MaintenanceBill $bill): RedirectResponse
    {
        $this->ownedByMember($bill->member_id);

        try {
            $order = $this->payments->createOrderForBill($bill, $request->user());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['bill' => $exception->getMessage()]);
        }

        return redirect()->route('member.bills.checkout', $order);
    }

    public function checkout(PaymentGatewayOrder $order): View
    {
        $this->ownedByMember($order->member_id);
        $order->load(['bill', 'member', 'society', 'payment']);

        return view('member.bills.checkout', [
            'order' => $order,
            'checkout' => $order->payload['checkout'] ?? [],
            'returnUrl' => $order->maintenance_bill_id
                ? route('member.bills.show', $order->maintenance_bill_id)
                : route('member.bills.index'),
            'receiptUrl' => $order->payment ? route('member.payments.show', $order->payment) : null,
            'webhookUrl' => route('webhooks.payments'),
        ]);
    }
}
