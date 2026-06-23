@extends('superadmin.layouts.app')

@section('title', 'Overdue')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Overdue</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="btn btn-primary"><i class="fas fa-bell"></i> Send Reminder</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.overview') }}">Revenue & Billing</a>
        <span class="breadcrumb-separator">/</span>
        <span>Overdue</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Overdue Amount</div>
            <div class="stat-value">&#8377; {{ number_format($totalOverdueAmount) }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 5.3% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Overdue Invoices</div>
            <div class="stat-value">{{ $totalOverdueInvoices }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 2 from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-calendar-times"></i></div>
        <div class="stat-info">
            <div class="stat-label">Overdue &gt; 30 Days</div>
            <div class="stat-value">{{ $overdue30Days }}</div>
            <div style="font-size: 12px; color: var(--danger); font-weight: 600;">&#8377; {{ number_format($overdue30Amount) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <div class="stat-label">Overdue &gt; 60 Days</div>
            <div class="stat-value">{{ $overdue60Days }}</div>
            <div style="font-size: 12px; color: var(--danger); font-weight: 600;">&#8377; {{ number_format($overdue60Amount) }}</div>
        </div>
    </div>
</div>

<div class="sub-nav-tabs">
    @foreach(['overview' => 'Overview', 'invoices' => 'Invoices', 'payments' => 'Payments', 'receipts' => 'Receipts', 'outstanding' => 'Outstanding', 'overdue' => 'Overdue', 'refunds' => 'Refunds'] as $route => $label)
    <a href="{{ route('superadmin.billing.' . $route) }}" class="sub-nav-tab {{ $route == 'overdue' ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-body">
            <div class="filter-bar">
                <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by member, invoice no., flat..."></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>Overdue Days</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>Building / Wing</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
            </div>

            <div style="margin-bottom: 16px;"><div class="section-title" style="font-size: 14px; margin: 0;"><i class="fas fa-list"></i> Overdue List</div></div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" class="form-check-input"></th>
                            <th>Invoice No.</th>
                            <th>Member / Flat</th>
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
                            <td><div class="user-name">{{ $inv->member_name }}</div><div class="user-email">{{ $inv->flat_number }}</div></td>
                            <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td>{{ $inv->due_date->format('d M Y') }}</td>
                            <td style="font-weight: 600;">&#8377; {{ number_format($inv->total_amount, 2) }}</td>
                            <td><span class="status-badge {{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                            <td><div style="display: flex; gap: 4px;"><button class="action-btn view"><i class="fas fa-eye"></i></button><button class="action-btn"><i class="fas fa-ellipsis-h"></i></button></div></td>
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
            <div class="card-header"><div class="card-title">Overdue Summary</div></div>
            <div class="card-body">
                @foreach(['Total Overdue Amount' => $totalOverdueAmount, 'Total Overdue Invoices' => $totalOverdueInvoices, 'Overdue &gt; 30 Days' => $overdue30Days, 'Overdue &gt; 60 Days' => $overdue60Days] as $label => $val)
                <div class="summary-item">
                    <span class="summary-label">{!! $label !!}</span>
                    <span class="summary-value" style="color: var(--danger); font-weight: 700;">{{ is_numeric($val) && $val > 1000 ? '&#8377; ' . number_format($val, 2) : $val }}</span>
                </div>
                @endforeach
                <div style="margin-top: 12px;"><a href="#" class="btn btn-outline-danger btn-sm" style="width: 100%;"><i class="fas fa-bell"></i> Send Reminder to All</a></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body" style="padding: 12px;">
                @foreach(['Send Reminder' => 'fa-bell', 'Record Payment' => 'fa-money-bill-wave', 'View Reports' => 'fa-chart-bar', 'Export Data' => 'fa-download'] as $label => $icon)
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
