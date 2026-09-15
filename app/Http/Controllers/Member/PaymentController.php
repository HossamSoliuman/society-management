<?php

namespace App\Http\Controllers\Member;

use App\Models\CollectionPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends PortalController
{
    public function index(Request $request): View
    {
        $member = $this->currentMember();

        $payments = $member->payments()
            ->with('maintenanceBill')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('receipt_number', 'like', "%{$term}%")->orWhere('bill_period', 'like', "%{$term}%"));
            })
            ->when($request->filled('mode'), fn ($q) => $q->where('payment_mode', $request->string('mode')))
            ->orderByDesc('receipt_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('member.payments.index', [
            'member' => $member,
            'payments' => $payments,
            'totalPaid' => (float) $member->payments()->where('status', 'paid')->sum('paid_amount'),
        ]);
    }

    public function show(CollectionPayment $payment): View
    {
        $this->ownedByMember($payment->member_id);
        $payment->loadMissing(['society', 'maintenanceBill']);

        return view('member.payments.show', [
            'payment' => $payment,
            'society' => $payment->society,
        ]);
    }

    public function pdf(CollectionPayment $payment): Response
    {
        $this->ownedByMember($payment->member_id);
        $payment->loadMissing(['society', 'maintenanceBill']);

        return Pdf::loadView('society.collections.receipts.pdf', [
            'payment' => $payment,
            'society' => $payment->society,
        ])->setPaper('a4')->download(str_replace(['/', '\\'], '-', $payment->receipt_number).'.pdf');
    }
}
