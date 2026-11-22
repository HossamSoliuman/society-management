@extends('society.layouts.app')

@section('title', 'Payment Receipt')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment Receipt</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Collections</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.receipts.index') }}">Payment Receipts</a>
                <span class="breadcrumb-separator">/</span>
                <span style="color: var(--primary);">{{ $payment->receipt_number }}</span>
            </div>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('society.collections.receipts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-chevron-left"></i> Back to Receipts</a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <form method="POST" action="{{ route('society.collections.receipts.email', $payment) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-paper-plane"></i> Email Receipt</button>
            </form>
            <a href="{{ route('society.collections.receipts.pdf', $payment) }}" class="btn btn-primary"><i class="fas fa-download"></i> Download PDF</a>
        </div>
    </div>
</div>

@if($payment->status === 'refunded')
    <div class="alert alert-warning">
        <i class="fas fa-rotate-left"></i>
        Refunded on {{ $payment->refunded_at?->format('d M Y, h:i A') ?? '—' }}{{ $payment->refund_reason ? ' · '.$payment->refund_reason : '' }}. The bills it paid are open again.
    </div>
@elseif((float) $payment->paid_amount > 0)
    <details class="card" style="margin-bottom: 20px;">
        <summary class="card-body" style="cursor: pointer; font-weight: 600;"><i class="fas fa-rotate-left" style="color: var(--danger); margin-right: 6px;"></i> Refund this payment</summary>
        <div class="card-body" style="padding-top: 0;">
            <form method="POST" action="{{ route('society.collections.receipts.refund', $payment) }}" data-confirm="Refund {{ format_inr($payment->paid_amount) }} on {{ $payment->receipt_number }}? The bills it paid will be reopened." data-confirm-label="Refund">
                @csrf
                <div class="form-group">
                    <label class="form-label">Reason <span class="required">*</span></label>
                    <input type="text" name="refund_reason" class="form-control" maxlength="255" required placeholder="e.g. Paid twice, cheque bounced">
                </div>
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-rotate-left"></i> Refund {{ format_inr($payment->paid_amount) }}</button>
            </form>
        </div>
    </details>
@endif

<div class="content-grid" style="grid-template-columns: 45% 1fr;">
    {{-- Left detail --}}
    <div>
        {{-- Receipt Information --}}
        <div class="card">
            <div class="card-body">
                <div class="card-icon-header">
                    <div class="icon" style="background: var(--primary-light); color: var(--primary);"><i class="fas fa-receipt"></i></div>
                    <div><div class="title">Receipt Information</div></div>
                </div>
                <div class="form-row" style="margin-bottom: 16px;">
                    <div><div class="cell-sub">Receipt No.</div><div style="color: var(--primary); font-weight: 700;">{{ $payment->receipt_number }}</div></div>
                    <div><div class="cell-sub">Receipt Date</div><div style="font-weight: 600;">{{ $payment->receipt_date?->format('d M Y, h:i A') }}</div></div>
                </div>
                <div class="form-row" style="margin-bottom: 0;">
                    <div><div class="cell-sub">Payment Mode</div><div style="font-weight: 600;">@if($payment->payment_mode)<i class="fas {{ $payment->paymentModeIcon() }}" style="color: var(--text-muted);"></i> {{ $payment->paymentModeLabel() }}@else &mdash; @endif</div></div>
                    <div><div class="cell-sub">Transaction / UTR No.</div><div style="font-weight: 600;">{{ $payment->transaction_utr ?: '—' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Member / Unit Details --}}
        <div class="card">
            <div class="card-body">
                <div class="card-icon-header">
                    <div class="icon" style="background: var(--info-light); color: #2563eb;"><i class="fas fa-user"></i></div>
                    <div><div class="title">Member / Unit Details</div></div>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <div class="avatar" style="width: 48px; height: 48px;">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($payment->member_name) }}&background=E84B1E&color=fff" alt="">
                        </div>
                        <div>
                            <div style="font-weight: 700;">{{ $payment->member_name }} ({{ $payment->member?->typeLabel() ?? 'Owner' }})</div>
                            <div style="font-size: 12px; color: var(--text-secondary);">{{ $payment->member_mobile ?: $payment->member?->mobile }} @if($payment->member_email ?: $payment->member?->email) | {{ $payment->member_email ?: $payment->member?->email }}@endif</div>
                            <div style="font-size: 12px; color: var(--text-secondary);">{{ $payment->unit_label }}</div>
                        </div>
                    </div>
                    <div style="border-left: 1px solid var(--border-color); padding-left: 20px;">
                        <div class="cell-sub">Member ID</div>
                        <div style="font-weight: 600; margin-bottom: 10px;">{{ $payment->member_code ?: '—' }}</div>
                        <div class="cell-sub">Unit Type</div>
                        <div style="font-weight: 600;">{{ $payment->unit_type ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Details --}}
        <div class="card">
            <div class="card-body">
                <div class="card-icon-header">
                    <div class="icon" style="background: #dcfce7; color: #16a34a;"><i class="fas fa-money-check-dollar"></i></div>
                    <div><div class="title">Payment Details</div></div>
                </div>
                <div class="form-row-3" style="margin-bottom: 16px;">
                    <div><div class="cell-sub">Bill Type</div><div style="font-weight: 600;">{{ $payment->bill_type }}</div></div>
                    <div><div class="cell-sub">Bill Period</div><div style="font-weight: 600;">{{ $payment->bill_period }}</div></div>
                    <div><div class="cell-sub">Due Date</div><div style="font-weight: 600;">{{ $payment->due_date?->format('d M Y') }}</div></div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; padding: 14px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); margin-bottom: 16px;">
                    <div><div class="cell-sub">Total Due (&#8377;)</div><div style="font-weight: 600;">{{ number_format((float) $payment->total_due, 2) }}</div></div>
                    <div><div class="cell-sub">Discount (&#8377;)</div><div style="font-weight: 600;">{{ number_format((float) $payment->discount, 2) }}</div></div>
                    <div><div class="cell-sub">Fine / Penalty (&#8377;)</div><div style="font-weight: 600;">{{ number_format((float) $payment->fine_penalty, 2) }}</div></div>
                    <div><div class="cell-sub">Paid Amount (&#8377;)</div><div style="font-weight: 700; color: var(--success);">{{ number_format((float) $payment->paid_amount, 2) }}</div></div>
                    <div><div class="cell-sub">Balance (&#8377;)</div><div style="font-weight: 600;">{{ number_format((float) $payment->balance_due, 2) }}</div></div>
                </div>
                <div class="cell-sub">Amount in Words</div>
                <div style="font-weight: 600;">{{ $payment->amountInWords() }}</div>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="card">
            <div class="card-body">
                <div class="card-icon-header">
                    <div class="icon" style="background: #ede9fe; color: #7c3aed;"><i class="fas fa-circle-info"></i></div>
                    <div><div class="title">Additional Information</div></div>
                </div>
                <div class="form-row" style="margin-bottom: 0;">
                    <div><div class="cell-sub">Reference No.</div><div style="font-weight: 600;">{{ $payment->reference_no ?: '—' }}</div></div>
                    <div><div class="cell-sub">Notes</div><div style="font-weight: 600;">{{ $payment->notes ?: '—' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right printable receipt --}}
    <div>
        @include('society.collections._receipt-template', ['payment' => $payment, 'society' => $society])
        <div class="info-box" style="margin-top: 16px;">
            <i class="fas fa-circle-info"></i>
            <span>This receipt has been recorded successfully.</span>
        </div>
    </div>
</div>

@if(request()->boolean('print'))
    @push('scripts')
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
    @endpush
@endif
@endsection
