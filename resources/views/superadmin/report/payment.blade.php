@extends('superadmin.layouts.app')

@section('title', 'Payment Report')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment Report</h1>
            <p class="page-subtitle">Analyze payment trends and methods.</p>
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
        <span>Payment Report</span>
    </div>
</div>

@php
    $total = $payments->count();
    $success = $payments->where('status', 'success')->count();
    $pending = $payments->where('status', 'pending')->count();
    $failed = $payments->where('status', 'failed')->count();
@endphp

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Payments</div>
            <div class="stat-value">{{ $total }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Successful</div>
            <div class="stat-value">{{ $success }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ $pending }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Failed</div>
            <div class="stat-value">{{ $failed }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by society, transaction ID..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option><option>Success</option><option>Pending</option><option>Failed</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Modes</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Mode</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->society->name ?? '—' }}</td>
                        <td style="font-weight: 600;">&#8377; {{ number_format($payment->amount) }}</td>
                        <td><span class="status-badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                        <td>{{ $payment->payment_mode ?? '—' }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $payment->transaction_id ?? '—' }}</td>
                        <td style="font-size: 12px;">{{ $payment->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">No payment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
