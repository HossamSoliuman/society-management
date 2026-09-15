@extends('member.layouts.app')

@section('title', 'Bill '.$billModel->bill_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $billModel->bill_number }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.bills.index') }}">My Bills</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $billModel->bill_number }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('member.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="{{ route('member.bills.pdf', $billModel) }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf"></i> Download PDF</a>
            @if($billModel->outstanding_amount > 0 && $billModel->status !== 'cancelled')
                <form method="POST" action="{{ route('member.bills.pay', $billModel) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fas fa-credit-card"></i> Pay &#8377; {{ format_inr($billModel->outstanding_amount) }} Online</button>
                </form>
            @endif
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<div class="content-grid" style="grid-template-columns: 1fr 320px;">
    <div class="card">
        <div class="card-body">
            <div class="receipt-preview">
                @include('society.billing._bill-template', ['design' => $design, 'bill' => $bill])
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Summary</div>
                <div class="review-row"><span class="review-label">Status</span><span class="review-value">@include('society.partials.status-badge', ['class' => $billModel->statusBadgeClass(), 'label' => $billModel->statusLabel()])</span></div>
                <div class="review-row"><span class="review-label">Due date</span><span class="review-value">{{ $billModel->due_date?->format('d M Y') }}</span></div>
                <div class="review-row"><span class="review-label">Total</span><span class="review-value">&#8377; {{ format_inr($billModel->total_amount) }}</span></div>
                <div class="review-row"><span class="review-label">Paid</span><span class="review-value">&#8377; {{ format_inr($billModel->collected_amount) }}</span></div>
                <div class="review-row"><span class="review-label">Outstanding</span><span class="review-value" style="font-weight: 700; color: var(--primary);">&#8377; {{ format_inr($billModel->outstanding_amount) }}</span></div>
            </div>
        </div>
        <div class="card" style="margin-top: 20px;">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Payments against this bill</div>
                @forelse($billModel->payments as $payment)
                    <div class="review-row">
                        <span class="review-label"><a href="{{ route('member.payments.show', $payment) }}">{{ $payment->receipt_number }}</a><br><small style="color: var(--text-muted);">{{ $payment->receipt_date?->format('d M Y') }}</small></span>
                        <span class="review-value">&#8377; {{ format_inr($payment->paid_amount) }}</span>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No payments yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
