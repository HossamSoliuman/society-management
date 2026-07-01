<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\CollectionPayment;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $society = Society::first();

        $query = CollectionPayment::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
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
        $society = $payment->society ?? Society::first();

        return view('society.collections.receipts.show', compact('payment', 'society'));
    }
}
