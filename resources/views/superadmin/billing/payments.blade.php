@extends('superadmin.layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payments</h1>
        </div>
        <div class="action-toolbar-right" style="display: flex; align-items: center; gap: 10px;">
            <span id="paymentsLoader" style="display: none; color: var(--text-muted); font-size: 12px;">
                <i class="fas fa-circle-notch fa-spin"></i> Updating…
            </span>
            <div class="header-search period-picker">
                <i class="fas fa-calendar search-icon"></i>
                <select data-period-select data-loader="paymentsLoader" data-fragment-prefix="payments-"
                        data-url="{{ route('superadmin.billing.payments.data') }}"
                        class="form-control" aria-label="Period"
                        style="padding-left: 36px; -webkit-appearance: none; appearance: none;">
                    @foreach($rangeOptions as $key => $label)
                        <option value="{{ $key }}" @selected($key === $range)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('superadmin.billing.payments.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Record Payment</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.overview') }}">Revenue & Billing</a>
        <span class="breadcrumb-separator">/</span>
        <span>Payments</span>
    </div>
</div>

<div class="stats-grid stats-grid-4" id="payments-stats">
    @include('superadmin.billing.partials.payments-stats', ['d' => $d])
</div>

<div class="sub-nav-tabs">
    @foreach(['overview' => 'Overview', 'invoices' => 'Invoices', 'payments' => 'Payments', 'receipts' => 'Receipts', 'outstanding' => 'Outstanding', 'overdue' => 'Overdue', 'refunds' => 'Refunds'] as $route => $label)
    <a href="{{ route('superadmin.billing.' . $route) }}" class="sub-nav-tab {{ $route == 'payments' ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-body">
            <div class="filter-bar">
                <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by member, invoice no., receipt no..."></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Methods</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Types</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search period-picker"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
            </div>

            <div style="margin-bottom: 16px;"><div class="section-title" style="font-size: 14px; margin: 0;"><i class="fas fa-list"></i> Payments List ({{ $payments->total() }})</div></div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Receipt No.</th>
                            <th>Member / Flat</th>
                            <th>Invoice No.</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $payment->receipt_number }}</td>
                            <td><div class="user-name">{{ $payment->member_name }}</div><div class="user-email">{{ $payment->flat_number }}</div></td>
                            <td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                            <td style="font-weight: 600;">&#8377; {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @php $methodIcons = ['UPI' => 'fa-mobile-alt', 'Bank Transfer' => 'fa-university', 'Credit Card' => 'fa-credit-card', 'Net Banking' => 'fa-globe', 'Cash' => 'fa-money-bill-wave']; @endphp
                                <span class="payment-method"><i class="fas {{ $methodIcons[$payment->payment_method] ?? 'fa-money-bill' }}"></i> {{ $payment->payment_method }}</span>
                            </td>
                            <td><span class="status-badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td><div style="display: flex; gap: 4px;">@if($payment->status === 'success' && $payment->refundableAmount() > 0)<a href="{{ route('superadmin.billing.refunds.create', ['payment' => $payment->id]) }}" class="action-btn" title="Refund"><i class="fas fa-rotate-left"></i></a>@endif</div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('superadmin.components.pagination', ['items' => $payments])
        </div>
    </div>

    <div>
        <div class="card" id="payments-summary" style="margin-bottom: 20px;">
            @include('superadmin.billing.partials.payments-summary', ['d' => $d])
        </div>

        <div class="card" id="payments-methods">
            @include('superadmin.billing.partials.payments-methods', ['d' => $d])
        </div>
    </div>
</div>
@endsection
