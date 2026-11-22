@extends('superadmin.layouts.app')

@section('title', 'Receipt '.$payment->receipt_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Receipt {{ $payment->receipt_number }}</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.billing.receipts') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.receipts') }}">Receipts</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $payment->receipt_number }}</span>
    </div>
</div>

<div class="card receipt-print-area" style="max-width: 760px;">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; margin-bottom: 24px;">
            <div>
                <div style="font-size: 20px; font-weight: 700;">{{ $company?->company_name ?? config('app.name') }}</div>
                @if($company?->address)<div style="color: var(--text-secondary); font-size: 13px;">{{ $company->address }}</div>@endif
                <div style="color: var(--text-secondary); font-size: 13px;">{{ collect([$company?->email, $company?->phone])->filter()->implode(' · ') }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 18px; font-weight: 700; letter-spacing: 1px;">PAYMENT RECEIPT</div>
                <div>{{ $payment->receipt_number }}</div>
                <div style="color: var(--text-secondary); font-size: 13px;">{{ $payment->payment_date?->format('d M Y') }}</div>
            </div>
        </div>

        <div class="review-row"><span class="review-label">Received From</span><span class="review-value">{{ $payment->society?->name ?? $payment->member_name ?? '—' }}</span></div>
        <div class="review-row"><span class="review-label">Against Invoice</span><span class="review-value">
            @if($payment->invoice)
                <a href="{{ route('superadmin.billing.invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
            @else
                —
            @endif
        </span></div>
        <div class="review-row"><span class="review-label">Payment Method</span><span class="review-value">{{ $payment->payment_method }}</span></div>
        <div class="review-row"><span class="review-label">Transaction / Reference</span><span class="review-value">{{ $payment->transaction_id ?? '—' }}</span></div>
        <div class="review-row"><span class="review-label">Amount Received</span><span class="review-value" style="font-weight: 700; color: var(--success);">{{ format_inr($payment->amount, 2) }}</span></div>
        <div class="review-row"><span class="review-label">Amount in Words</span><span class="review-value">{{ amount_in_words_inr($payment->amount) }}</span></div>
        @if($payment->refunds->isNotEmpty())
            <div class="review-row"><span class="review-label">Refunded</span><span class="review-value" style="color: var(--danger);">{{ format_inr($payment->refunds->where('status', 'completed')->sum('amount'), 2) }}</span></div>
        @endif
        @if($payment->invoice)
            <div class="review-row"><span class="review-label">Invoice Outstanding</span><span class="review-value">{{ format_inr($payment->invoice->outstanding_amount, 2) }}</span></div>
        @endif
        <div class="review-row"><span class="review-label">Status</span><span class="review-value"><span class="status-badge {{ $payment->status === 'success' ? 'paid' : $payment->status }}">{{ ucfirst($payment->status) }}</span></span></div>
        @if($payment->notes)
            <div class="review-row"><span class="review-label">Notes</span><span class="review-value">{{ $payment->notes }}</span></div>
        @endif
    </div>
</div>
@endsection
