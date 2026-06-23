@extends('superadmin.layouts.app')

@section('title', 'Subscription Management')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Subscription Management</h1>
            <p class="page-subtitle">Manage all society subscriptions, track renewal dates, and monitor subscription status.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="{{ route('superadmin.subscription.subscriptions.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Subscription</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Subscription Management</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-cube"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Subscriptions</div>
            <div class="stat-value">128</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 8.2% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Subscriptions</div>
            <div class="stat-value">98</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 6.7% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expiring Soon</div>
            <div class="stat-value">14</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 12.5% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expired Subscriptions</div>
            <div class="stat-value">16</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 5.3% from last month</div>
        </div>
    </div>
</div>

<div class="tabs">
    <a href="#" class="tab active">All Subscriptions</a>
    <a href="#" class="tab">Active</a>
    <a href="#" class="tab">Expiring Soon</a>
    <a href="#" class="tab">Expired</a>
    <a href="#" class="tab">Cancelled</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;">
                <div class="header-search" style="max-width: 100%;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search by society or building...">
                </div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select class="form-control"><option>All Plans</option><option>Basic</option><option>Standard</option><option>Premium</option></select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select class="form-control"><option>All Status</option><option>Active</option><option>Expired</option></select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select class="form-control"><option>All Buildings</option></select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <div class="header-search" style="max-width: 180px;"><i class="fas fa-calendar search-icon"></i><input type="text" placeholder="01 May 2025 - 31 May 2025"></div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society / Building</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Amount (&#8377;)</th>
                        <th>Next Renewal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    <tr>
                        <td>
                            <div class="society-row">
                                <div class="society-icon"><i class="fas fa-building"></i></div>
                                <div class="society-details">
                                    <h4>{{ $sub->society->name ?? 'N/A' }}</h4>
                                    <p>{{ $sub->building_name ?? 'All Buildings' }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="prefix-tag">{{ $sub->plan->name ?? 'N/A' }}</span></td>
                        <td>{{ $sub->start_date ? $sub->start_date->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $sub->end_date ? $sub->end_date->format('d M Y') : 'N/A' }}</td>
                        <td><span class="status-badge {{ $sub->status }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span></td>
                        <td style="font-weight: 600;">&#8377; {{ number_format($sub->plan->amount ?? 0, 2) }}</td>
                        <td>
                            @if($sub->end_date)
                                @php $daysLeft = now()->diffInDays($sub->end_date, false); @endphp
                                <div>{{ $sub->end_date->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: {{ $daysLeft <= 30 ? 'var(--danger)' : 'var(--primary)' }};">
                                    @if($daysLeft > 0) in {{ $daysLeft }} days @elseif($daysLeft == 0) Today @else - @endif
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit"><i class="fas fa-pen"></i></button>
                                <button class="action-btn delete"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="empty-state" style="padding: 40px;">No subscriptions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $subscriptions])
    </div>
    <div class="card-footer"><i class="fas fa-info-circle" style="margin-right: 6px;"></i> Manage all society subscriptions, track renewal dates, and monitor subscription status.</div>
</div>
@endsection
