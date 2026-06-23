@extends('superadmin.layouts.app')

@section('title', 'Receipts')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Receipts</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Create Receipt</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.overview') }}">Revenue & Billing</a>
        <span class="breadcrumb-separator">/</span>
        <span>Receipts</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-wallet"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Collections (This Month)</div>
            <div class="stat-value">&#8377; {{ number_format($totalCollections) }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 10.8% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Receipts Issued (This Month)</div>
            <div class="stat-value">{{ $receiptsIssued }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 10.8% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <div class="stat-label">Pending Receipts</div>
            <div class="stat-value">{{ $pendingReceipts }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 2 from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Cancelled Receipts</div>
            <div class="stat-value">{{ $cancelledReceipts }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Same as last month</div>
        </div>
    </div>
</div>

<div class="sub-nav-tabs">
    @foreach(['overview' => 'Overview', 'invoices' => 'Invoices', 'payments' => 'Payments', 'receipts' => 'Receipts', 'outstanding' => 'Outstanding', 'overdue' => 'Overdue', 'refunds' => 'Refunds'] as $route => $label)
    <a href="{{ route('superadmin.billing.' . $route) }}" class="sub-nav-tab {{ $route == 'receipts' ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-body">
            <div class="filter-bar">
                <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search receipt no., member, invoice..."></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Methods</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
            </div>

            <div style="margin-bottom: 16px;"><div class="section-title" style="font-size: 14px; margin: 0;"><i class="fas fa-list"></i> Receipts List</div></div>

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
                            <td><div style="display: flex; gap: 4px;"><button class="action-btn view"><i class="fas fa-eye"></i></button><button class="action-btn"><i class="fas fa-print"></i></button></div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('superadmin.components.pagination', ['items' => $payments])
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"><div class="card-title">Receipt Summary</div></div>
            <div class="card-body">
                @foreach(['Total Receipts Issued' => $receiptsIssued, 'Total Amount Collected' => $totalCollections, 'Successful Payments' => $receiptsIssued - $pendingReceipts, 'Pending Receipts' => $pendingReceipts, 'Cancelled Receipts' => $cancelledReceipts] as $label => $val)
                <div class="summary-item">
                    <span class="summary-label">{{ $label }}</span>
                    <span class="summary-value" style="font-weight: 700; color: {{ is_numeric($val) && $val > 1000 ? 'var(--text-primary)' : 'var(--text-primary)' }};">{{ is_numeric($val) && $val > 1000 ? '&#8377; ' . number_format($val, 2) : $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body" style="padding: 12px;">
                @foreach(['Create Receipt' => 'fa-file-invoice', 'Bulk Receipt' => 'fa-copy', 'Receipt Settings' => 'fa-cog'] as $label => $icon)
                <a href="#" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: var(--radius); text-decoration: none; color: var(--text-primary); font-size: 13px; font-weight: 500; transition: all 0.2s; margin-bottom: 4px;">
                    <span style="display: flex; align-items: center; gap: 10px;"><i class="fas {{ $icon }}" style="color: var(--primary);"></i> {{ $label }}</span>
                    <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 10px;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
