<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\CollectionPayment;
use App\Notifications\PaymentReceiptIssued;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class PaymentReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $query = CollectionPayment::query()
            ->forSociety($society)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('member_name', 'like', "%{$term}%")
                        ->orWhere('flat_number', 'like', "%{$term}%")
                        ->orWhere('receipt_number', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('receipt_date')
            ->orderByDesc('id');

        $payments = $query->paginate(8)->withQueryString();

        return view('society.collections.receipts.index', compact('society', 'payments'));
    }

    public function show(CollectionPayment $payment): View
    {
        $society = $payment->society ?? $this->currentSociety();

        return view('society.collections.receipts.show', compact('payment', 'society'));
    }

    public function pdf(CollectionPayment $payment): Response
    {
        $payment->loadMissing(['society', 'maintenanceBill']);

        return Pdf::loadView('society.collections.receipts.pdf', [
            'payment' => $payment,
            'society' => $payment->society ?? $this->currentSociety(),
        ])->setPaper('a4')->download(str_replace(['/', '\\'], '-', $payment->receipt_number).'.pdf');
    }

    public function email(CollectionPayment $payment): RedirectResponse
    {
        $email = $payment->member_email ?: $payment->member?->email;

        if (! $email) {
            return back()->with('error', 'No email address is on file for this member.');
        }

        Notification::route('mail', $email)->notify(new PaymentReceiptIssued($payment));

        return back()->with('success', "Receipt {$payment->receipt_number} emailed to {$email}.");
    }
}
