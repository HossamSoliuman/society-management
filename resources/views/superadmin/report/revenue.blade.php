@extends('superadmin.layouts.app')

@section('title', 'Revenue Report')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Revenue Report</h1>
            <p class="page-subtitle">Track revenue and financial performance.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.reports.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Reports</a>
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.reports.index') }}">Reports</a>
        <span class="breadcrumb-separator">/</span>
        <span>Revenue Report</span>
    </div>
</div>

@php
    $totalRevenue = $payments->sum('amount');
    $thisMonth = $payments->filter(fn($p) => $p->created_at->isCurrentMonth())->sum('amount');
    $lastMonth = $payments->filter(fn($p) => $p->created_at->isLastMonth())->sum('amount');
@endphp

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">&#8377; {{ number_format($totalRevenue) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">This Month</div>
            <div class="stat-value">&#8377; {{ number_format($thisMonth) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-calendar"></i></div>
        <div class="stat-info">
            <div class="stat-label">Last Month</div>
            <div class="stat-value">&#8377; {{ number_format($lastMonth) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value">{{ $payments->count() }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by society, transaction ID..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Modes</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" placeholder="Date range..." readonly></div></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->society->name ?? '—' }}</td>
                        <td style="font-weight: 600; color: var(--success);">&#8377; {{ number_format($payment->amount) }}</td>
                        <td>{{ $payment->payment_mode ?? '—' }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $payment->transaction_id ?? '—' }}</td>
                        <td style="font-size: 12px;">{{ $payment->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">No payment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
