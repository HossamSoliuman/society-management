@extends('superadmin.layouts.app')

@section('title', 'Revenue & Billing')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Revenue & Billing</h1>
        </div>
        <div class="action-toolbar-right" style="display: flex; align-items: center; gap: 10px;">
            <span id="billingLoader" style="display: none; color: var(--text-muted); font-size: 12px;">
                <i class="fas fa-circle-notch fa-spin"></i> Updating…
            </span>
            <div class="header-search period-picker">
                <i class="fas fa-calendar search-icon"></i>
                <select data-period-select data-loader="billingLoader" data-fragment-prefix="billing-"
                        data-url="{{ route('superadmin.billing.overview.data') }}"
                        class="form-control" aria-label="Period"
                        style="padding-left: 36px; -webkit-appearance: none; appearance: none;">
                    @foreach($rangeOptions as $key => $label)
                        <option value="{{ $key }}" @selected($key === $range)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('superadmin.billing.invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create Invoice</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Revenue & Billing</span>
    </div>
</div>

<div class="stats-grid stats-grid-4" id="billing-stats">
    @include('superadmin.billing.partials.overview-stats', ['d' => $d])
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.billing.overview') }}" class="sub-nav-tab active">Overview</a>
    <a href="{{ route('superadmin.billing.invoices') }}" class="sub-nav-tab">Invoices</a>
    <a href="{{ route('superadmin.billing.payments') }}" class="sub-nav-tab">Payments</a>
    <a href="{{ route('superadmin.billing.receipts') }}" class="sub-nav-tab">Receipts</a>
    <a href="{{ route('superadmin.billing.outstanding') }}" class="sub-nav-tab">Outstanding</a>
    <a href="{{ route('superadmin.billing.overdue') }}" class="sub-nav-tab">Overdue</a>
    <a href="{{ route('superadmin.billing.refunds') }}" class="sub-nav-tab">Refunds</a>
</div>

<div class="grid-3">
    <div class="card" id="billing-revenue" style="grid-column: span 2;">
        @include('superadmin.billing.partials.overview-revenue', ['d' => $d])
    </div>

    <div class="card" id="billing-categories">
        @include('superadmin.billing.partials.overview-categories', ['d' => $d])
    </div>
</div>

<div class="grid-3">
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <div class="card-title">Recent Invoices</div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Invoice No.</th>
                        <th>Flat / Unit</th>
                        <th>Member Name</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentInvoices as $invoice)
                    <tr>
                        <td style="padding-left: 20px;">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->flat_number }}</td>
                        <td>{{ $invoice->member_name }}</td>
                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                        <td style="font-weight: 600;">&#8377; {{ number_format($invoice->total_amount, 2) }}</td>
                        <td><span class="status-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('superadmin.billing.invoices.show', $invoice) }}" class="action-btn view" title="View invoice"><i class="fas fa-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('superadmin.billing.invoices') }}" class="btn-link" style="display: flex; align-items: center; gap: 6px;">
                View all invoices <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
            </a>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title">Collection Status</div>
            </div>
            <div class="card-body" style="display: flex; align-items: center; gap: 20px;">
                <x-chart type="doughnut" :size="120" format="inr" label="Collection status"
                    :labels="['Collected', 'Outstanding', 'Overdue']"
                    :datasets="[['data' => [$collectionStatus['collected'], $collectionStatus['outstanding'], $collectionStatus['overdue']], 'colors' => ['#10B981', '#F59E0B', '#EF4444']]]"
                    :center-value="$collectionStatus['collected_percent'].'%'" center-label="Collected" />
                <div class="chart-legend" style="flex: 1;">
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #10B981;"></span>
                        <span class="legend-label">Collected</span>
                        <span class="legend-value">&#8377; {{ number_format($collectionStatus['collected']) }} ({{ $collectionStatus['collected_percent'] }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #F59E0B;"></span>
                        <span class="legend-label">Outstanding</span>
                        <span class="legend-value">&#8377; {{ number_format($collectionStatus['outstanding']) }} ({{ $collectionStatus['outstanding_percent'] }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #EF4444;"></span>
                        <span class="legend-label">Overdue</span>
                        <span class="legend-value">&#8377; {{ number_format($collectionStatus['overdue']) }} ({{ $collectionStatus['overdue_percent'] }}%)</span>
                    </div>
                </div>
            </div>
            <div style="padding: 0 20px 16px;">
                <a href="{{ route('superadmin.billing.outstanding') }}" class="btn-link" style="font-size: 12px;">View details <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            <div class="card-body" style="padding: 12px;">
                @php
                    $actions = [
                        ['label' => 'Create Invoice', 'icon' => 'fa-file-invoice', 'url' => route('superadmin.billing.invoices.create')],
                        ['label' => 'Record Payment', 'icon' => 'fa-money-bill-wave', 'url' => route('superadmin.billing.payments.create')],
                        ['label' => 'Issue Refund', 'icon' => 'fa-rotate-left', 'url' => route('superadmin.billing.refunds.create')],
                        ['label' => 'Overdue Invoices', 'icon' => 'fa-exclamation-triangle', 'url' => route('superadmin.billing.overdue')],
                    ];
                @endphp
                @foreach($actions as $action)
                <a href="{{ $action['url'] }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: var(--radius); text-decoration: none; color: var(--text-primary); font-size: 13px; font-weight: 500; transition: all 0.2s; margin-bottom: 4px;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 32px; height: 32px; border-radius: var(--radius); background: var(--primary-light); display: flex; align-items: center; justify-content: center;"><i class="fas {{ $action['icon'] }}" style="color: var(--primary); font-size: 13px;"></i></span>
                        {{ $action['label'] }}
                    </span>
                    <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 10px;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
