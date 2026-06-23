@extends('superadmin.layouts.app')

@section('title', 'Subscription Report')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Subscription Report</h1>
            <p class="page-subtitle">Monitor subscription status and renewals.</p>
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
        <span>Subscription Report</span>
    </div>
</div>

@php
    $total = $subscriptions->count();
    $active = $subscriptions->where('status', 'active')->count();
    $expired = $subscriptions->where('status', 'expired')->count();
    $trial = $subscriptions->where('status', 'trial')->count();
@endphp

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-credit-card"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Subscriptions</div>
            <div class="stat-value">{{ $total }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active</div>
            <div class="stat-value">{{ $active }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expired</div>
            <div class="stat-value">{{ $expired }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-flask"></i></div>
        <div class="stat-info">
            <div class="stat-label">Trial</div>
            <div class="stat-value">{{ $trial }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by society, plan..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option><option>Active</option><option>Expired</option><option>Trial</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Plans</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Billing Cycle</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->society->name ?? '—' }}</td>
                        <td>{{ $subscription->plan->name ?? '—' }}</td>
                        <td style="font-size: 12px;">{{ $subscription->start_date ? \Carbon\Carbon::parse($subscription->start_date)->format('d M Y') : '—' }}</td>
                        <td style="font-size: 12px;">{{ $subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date)->format('d M Y') : '—' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $subscription->billing_cycle ?? '—')) }}</td>
                        <td><span class="status-badge {{ $subscription->status }}">{{ ucfirst($subscription->status) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">No subscriptions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
