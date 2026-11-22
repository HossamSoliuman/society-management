@extends('superadmin.layouts.app')

@section('title', 'Invoice '.$invoice->invoice_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Invoice {{ $invoice->invoice_number }}</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.billing.invoices') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            @if($invoice->isOpen())
                <a href="{{ route('superadmin.billing.payments.create', ['invoice' => $invoice->id]) }}" class="btn btn-primary"><i class="fas fa-indian-rupee-sign"></i> Record Payment</a>
            @endif
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.invoices') }}">Invoices</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $invoice->invoice_number }}</span>
    </div>
</div>

<div class="card receipt-print-area">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; margin-bottom: 24px;">
            <div>
                <div style="font-size: 20px; font-weight: 700;">{{ $company?->company_name ?? config('app.name') }}</div>
                @if($company?->address)<div style="color: var(--text-secondary); font-size: 13px;">{{ $company->address }}</div>@endif
                <div style="color: var(--text-secondary); font-size: 13px;">{{ collect([$company?->email, $company?->phone])->filter()->implode(' · ') }}</div>
                @if($company?->gst_number)<div style="color: var(--text-secondary); font-size: 13px;">GSTIN {{ $company->gst_number }}</div>@endif
            </div>
            <div style="text-align: right;">
                <div style="font-size: 18px; font-weight: 700; letter-spacing: 1px;">INVOICE</div>
                <div>{{ $invoice->invoice_number }}</div>
                <span class="status-badge {{ $invoice->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $invoice->status)) }}</span>
            </div>
        </div>

        <div class="grid-2" style="margin-bottom: 24px;">
            <div>
                <div class="review-row"><span class="review-label">Billed To</span><span class="review-value">{{ $invoice->society?->name ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Contact</span><span class="review-value">{{ $invoice->member_name ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Subscription</span><span class="review-value">{{ $invoice->subscription?->subscription_number ?? '—' }}</span></div>
            </div>
            <div>
                <div class="review-row"><span class="review-label">Invoice Date</span><span class="review-value">{{ $invoice->invoice_date?->format('d M Y') }}</span></div>
                <div class="review-row"><span class="review-label">Due Date</span><span class="review-value">{{ $invoice->due_date?->format('d M Y') }}</span></div>
                <div class="review-row"><span class="review-label">Category</span><span class="review-value">{{ $invoice->category ?? $invoice->invoice_type ?? '—' }}</span></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Description</th><th style="text-align: right;">Amount</th></tr>
                </thead>
                <tbody>
                    <tr><td>{{ $invoice->category ?? 'Subscription' }}{{ $invoice->subscription?->plan ? ' · '.$invoice->subscription->plan->name : '' }}</td><td style="text-align: right;">{{ format_inr($invoice->amount, 2) }}</td></tr>
                    <tr><td>Tax</td><td style="text-align: right;">{{ format_inr($invoice->tax_amount, 2) }}</td></tr>
                    <tr><td style="font-weight: 700;">Total</td><td style="text-align: right; font-weight: 700;">{{ format_inr($invoice->total_amount, 2) }}</td></tr>
                    <tr><td>Paid</td><td style="text-align: right; color: var(--success);">{{ format_inr($invoice->paid_amount, 2) }}</td></tr>
                    <tr><td style="font-weight: 700;">Outstanding</td><td style="text-align: right; font-weight: 700; color: var(--warning);">{{ format_inr($invoice->outstanding_amount, 2) }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="card-title" style="margin: 24px 0 12px;">Payments</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Receipt</th><th>Date</th><th>Method</th><th>Reference</th><th style="text-align: right;">Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($invoice->payments as $payment)
                        <tr>
                            <td><a href="{{ route('superadmin.billing.receipts.show', $payment) }}">{{ $payment->receipt_number }}</a></td>
                            <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->transaction_id ?? '—' }}</td>
                            <td style="text-align: right;">{{ format_inr($payment->amount, 2) }}</td>
                            <td><span class="status-badge {{ $payment->status === 'success' ? 'paid' : $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
