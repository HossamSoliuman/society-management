@extends('member.layouts.app')

@section('title', 'Receipt '.$payment->receipt_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment Receipt</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.payments.index') }}">My Payments</a>
                <span class="breadcrumb-separator">/</span>
                <span style="color: var(--primary);">{{ $payment->receipt_number }}</span>
            </div>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('member.payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-chevron-left"></i> Back</a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <a href="{{ route('member.payments.pdf', $payment) }}" class="btn btn-primary"><i class="fas fa-download"></i> Download PDF</a>
        </div>
    </div>
</div>

<div class="content-grid" style="grid-template-columns: 1fr 1.2fr;">
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Payment Details</div>
                <div class="review-row"><span class="review-label">Receipt No.</span><span class="review-value">{{ $payment->receipt_number }}</span></div>
                <div class="review-row"><span class="review-label">Date</span><span class="review-value">{{ $payment->receipt_date?->format('d M Y') }}</span></div>
                <div class="review-row"><span class="review-label">Bill</span><span class="review-value">
                    @if($payment->maintenanceBill)
                        <a href="{{ route('member.bills.show', $payment->maintenanceBill) }}">{{ $payment->maintenanceBill->bill_number }}</a>
                    @else
                        {{ $payment->bill_period ?: '—' }}
                    @endif
                </span></div>
                <div class="review-row"><span class="review-label">Mode</span><span class="review-value">{{ $payment->paymentModeLabel() }}</span></div>
                <div class="review-row"><span class="review-label">Reference</span><span class="review-value">{{ $payment->reference_no ?: ($payment->transaction_utr ?: '—') }}</span></div>
                <div class="review-row"><span class="review-label">Amount</span><span class="review-value" style="font-weight: 700; color: var(--primary);">&#8377; {{ format_inr($payment->paid_amount) }}</span></div>
                <div class="review-row"><span class="review-label">Status</span><span class="review-value">@include('society.partials.status-badge', ['class' => $payment->statusBadgeClass(), 'label' => $payment->statusLabel()])</span></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 12px;">{{ $payment->amountInWords() }}</div>
            </div>
        </div>
    </div>
    <div>
        @include('society.collections._receipt-template', ['payment' => $payment, 'society' => $society])
    </div>
</div>
@endsection
