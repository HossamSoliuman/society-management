@extends('member.layouts.app')

@section('title', 'My Payments')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Payments</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>My Payments</span>
            </div>
        </div>
        <div class="stat-card" style="padding: 10px 16px;">
            <div class="stat-info">
                <div class="stat-label">Total paid</div>
                <div class="stat-value" style="font-size: 18px;">&#8377; {{ format_inr($totalPaid) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('member.payments.index') }}" style="display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search</label>
                <div class="header-search" style="max-width: none;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Receipt number or bill period">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Payment mode</label>
                <select name="mode" class="form-control">
                    <option value="">All</option>
                    @foreach(['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'net_banking' => 'Net Banking', 'cheque' => 'Cheque', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" {{ request('mode') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('member.payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Receipt No.</th>
                        <th>Date</th>
                        <th>Bill</th>
                        <th>Mode</th>
                        <th style="text-align: right;">Amount (&#8377;)</th>
                        <th>Status</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td style="padding-left: 20px;"><a href="{{ route('member.payments.show', $payment) }}" style="font-weight: 600;">{{ $payment->receipt_number }}</a></td>
                            <td>{{ $payment->receipt_date?->format('d M Y') }}</td>
                            <td>
                                @if($payment->maintenanceBill)
                                    <a href="{{ route('member.bills.show', $payment->maintenanceBill) }}">{{ $payment->maintenanceBill->bill_number }}</a>
                                @else
                                    {{ $payment->bill_period ?: '—' }}
                                @endif
                            </td>
                            <td><i class="fas {{ $payment->paymentModeIcon() }}" style="color: var(--text-muted); margin-right: 4px;"></i>{{ $payment->paymentModeLabel() }}{{ $payment->is_online ? ' (online)' : '' }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ format_inr($payment->paid_amount) }}</td>
                            <td>@include('society.partials.status-badge', ['class' => $payment->statusBadgeClass(), 'label' => $payment->statusLabel()])</td>
                            <td style="text-align: right; padding-right: 20px;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('member.payments.show', $payment) }}" class="btn btn-outline-secondary btn-sm" title="View receipt"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('member.payments.pdf', $payment) }}" class="btn btn-outline-secondary btn-sm" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">No payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $payments, 'unit' => 'payments'])
@endsection
