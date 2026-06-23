@extends('superadmin.layouts.app')

@section('title', 'Refunds')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Refunds</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Process Refund</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.overview') }}">Revenue & Billing</a>
        <span class="breadcrumb-separator">/</span>
        <span>Refunds</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-undo"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Refund Amount</div>
            <div class="stat-value">&#8377; {{ number_format($totalRefundAmount) }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 12.5% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Refunds</div>
            <div class="stat-value">{{ $totalRefunds }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 5.3% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <div class="stat-label">Pending Refunds</div>
            <div class="stat-value">{{ $pendingRefunds }}</div>
            <div style="font-size: 12px; color: var(--warning); font-weight: 600;">&#8377; {{ number_format($pendingRefundAmount) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Failed Refunds</div>
            <div class="stat-value">{{ $failedRefunds }}</div>
            <div style="font-size: 12px; color: var(--danger); font-weight: 600;">&#8377; {{ number_format($failedRefundAmount) }}</div>
        </div>
    </div>
</div>

<div class="sub-nav-tabs">
    @foreach(['overview' => 'Overview', 'invoices' => 'Invoices', 'payments' => 'Payments', 'receipts' => 'Receipts', 'outstanding' => 'Outstanding', 'overdue' => 'Overdue', 'refunds' => 'Refunds'] as $route => $label)
    <a href="{{ route('superadmin.billing.' . $route) }}" class="sub-nav-tab {{ $route == 'refunds' ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-body">
            <div class="filter-bar">
                <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search refund no., member, invoice..."></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Methods</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
            </div>

            <div style="margin-bottom: 16px;"><div class="section-title" style="font-size: 14px; margin: 0;"><i class="fas fa-list"></i> Refunds List</div></div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" class="form-check-input"></th>
                            <th>Refund No.</th>
                            <th>Member / Flat</th>
                            <th>Invoice No.</th>
                            <th>Amount</th>
                            <th>Refund Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($refunds as $refund)
                        <tr>
                            <td><input type="checkbox" class="form-check-input"></td>
                            <td>{{ $refund->refund_number }}</td>
                            <td><div class="user-name">{{ $refund->member_name }}</div><div class="user-email">{{ $refund->flat_number }}</div></td>
                            <td>{{ $refund->payment->receipt_number ?? 'N/A' }}</td>
                            <td style="font-weight: 600;">&#8377; {{ number_format($refund->amount, 2) }}</td>
                            <td>{{ $refund->refund_date ? $refund->refund_date->format('d M Y') : 'N/A' }}</td>
                            <td><span class="status-badge {{ $refund->status }}">{{ ucfirst($refund->status) }}</span></td>
                            <td><div style="display: flex; gap: 4px;"><button class="action-btn view"><i class="fas fa-eye"></i></button><button class="action-btn"><i class="fas fa-ellipsis-h"></i></button></div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('superadmin.components.pagination', ['items' => $refunds])
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"><div class="card-title">Refund Summary</div></div>
            <div class="card-body">
                @foreach(['Total Refund Amount' => $totalRefundAmount, 'Total Refunds' => $totalRefunds, 'Pending Refunds' => $pendingRefundAmount, 'Failed Refunds' => $failedRefundAmount] as $label => $val)
                <div class="summary-item">
                    <span class="summary-label">{{ $label }}</span>
                    <span class="summary-value" style="color: {{ $loop->index == 2 ? 'var(--warning)' : ($loop->index == 3 ? 'var(--danger)' : 'var(--text-primary)') }}; font-weight: 700;">{{ is_numeric($val) && $val > 100 ? '&#8377; ' . number_format($val, 2) : $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body" style="padding: 12px;">
                @foreach(['Process Refund' => 'fa-plus-circle', 'Refund Report' => 'fa-chart-bar', 'Refund Settings' => 'fa-cog'] as $label => $icon)
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
