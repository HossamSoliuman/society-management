@extends('superadmin.layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Invoices</h1>
            <p class="page-subtitle">Create, manage and track all invoices.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Create Invoice</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.overview') }}">Revenue & Billing</a>
        <span class="breadcrumb-separator">/</span>
        <span>Invoices</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Invoices</div>
            <div class="stat-value">{{ $totalInvoices }}</div>
            <div style="font-size: 11px; color: var(--primary);">This Month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Paid Invoices</div>
            <div class="stat-value">{{ $paidInvoices }}</div>
            <div style="font-size: 12px; color: var(--success); font-weight: 600;">&#8377; {{ number_format($paidAmount) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Pending Invoices</div>
            <div class="stat-value">{{ $pendingInvoices }}</div>
            <div style="font-size: 12px; color: var(--warning); font-weight: 600;">&#8377; {{ number_format($pendingAmount) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Overdue Invoices</div>
            <div class="stat-value">{{ $overdueInvoices }}</div>
            <div style="font-size: 12px; color: var(--danger); font-weight: 600;">&#8377; {{ number_format($overdueAmount) }}</div>
        </div>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.billing.overview') }}" class="sub-nav-tab">Overview</a>
    <a href="{{ route('superadmin.billing.invoices') }}" class="sub-nav-tab active">Invoices</a>
    <a href="{{ route('superadmin.billing.payments') }}" class="sub-nav-tab">Payments</a>
    <a href="{{ route('superadmin.billing.receipts') }}" class="sub-nav-tab">Receipts</a>
    <a href="{{ route('superadmin.billing.outstanding') }}" class="sub-nav-tab">Outstanding</a>
    <a href="{{ route('superadmin.billing.overdue') }}" class="sub-nav-tab">Overdue</a>
    <a href="{{ route('superadmin.billing.refunds') }}" class="sub-nav-tab">Refunds</a>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-body">
            <div class="filter-bar">
                <div class="filter-item" style="flex: 1;">
                    <div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search invoice no., member, flat..."></div>
                </div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Types</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>Building / Wing</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" class="form-check-input"></th>
                            <th>Invoice No.</th>
                            <th>Member / Flat</th>
                            <th>Invoice Type</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                        <tr>
                            <td><input type="checkbox" class="form-check-input"></td>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>
                                <div class="user-name">{{ $inv->member_name }}</div>
                                <div class="user-email">{{ $inv->flat_number }}</div>
                            </td>
                            <td>{{ $inv->invoice_type }}</td>
                            <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td>{{ $inv->due_date->format('d M Y') }}</td>
                            <td style="font-weight: 600;">&#8377; {{ number_format($inv->total_amount, 2) }}</td>
                            <td><span class="status-badge {{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-download"></i></button>
                                    <button class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('superadmin.components.pagination', ['items' => $invoices])
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"><div class="card-title">Invoice Summary</div></div>
            <div class="card-body">
                @php $summary = ['Total Amount' => $totalInvoices * 4250, 'Paid Amount' => $paidAmount, 'Pending Amount' => $pendingAmount, 'Overdue Amount' => $overdueAmount]; @endphp
                @foreach($summary as $label => $val)
                <div class="summary-item">
                    <span class="summary-label">{{ $label }}</span>
                    <span class="summary-value" style="color: {{ $loop->index == 3 ? 'var(--danger)' : ($loop->index == 1 ? 'var(--success)' : 'var(--text-primary)') }}; font-weight: 700;">&#8377; {{ number_format($val, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body" style="padding: 12px;">
                @foreach(['Create Invoice' => 'fa-file-invoice', 'Bulk Invoice' => 'fa-copy', 'Recurring Invoices' => 'fa-sync', 'Invoice Settings' => 'fa-cog'] as $label => $icon)
                <a href="#" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: var(--radius); text-decoration: none; color: var(--text-primary); font-size: 13px; font-weight: 500; transition: all 0.2s; margin-bottom: 4px;">
                    <span style="display: flex; align-items: center; gap: 10px;"><i class="fas {{ $icon }}" style="color: var(--primary);"></i> {{ $label }}</span>
                    <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 10px;"></i>
                </a>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Invoice Types</div></div>
            <div class="card-body">
                @php $types = ['Maintenance' => ['color' => 'success', 'count' => 156], 'Sinking Fund' => ['color' => 'info', 'count' => 48], 'Water Charges' => ['color' => 'primary', 'count' => 28], 'Parking Charges' => ['color' => 'warning', 'count' => 16], 'Other Charges' => ['color' => 'secondary', 'count' => 8]]; @endphp
                @foreach($types as $type => $data)
                <div class="summary-item">
                    <span style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-circle" style="font-size: 6px; color: var(--{{ $data['color'] }});"></i> {{ $type }}</span>
                    <span class="summary-value">{{ $data['count'] }}</span>
                </div>
                @endforeach
                <div style="margin-top: 12px;"><a href="#" class="btn-link" style="font-size: 12px;">View all types <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a></div>
            </div>
        </div>
    </div>
</div>
@endsection
