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
            <a href="{{ route('superadmin.subscription.subscriptions.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Subscription</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Subscription Management</span>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-cube"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Subscriptions</div>
            <div class="stat-value">{{ $statusCounts->sum() }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">All time</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Subscriptions</div>
            <div class="stat-value">{{ $statusCounts['active'] ?? 0 }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Currently active</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expiring Soon</div>
            <div class="stat-value">{{ $statusCounts['expiring_soon'] ?? 0 }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Within 30 days</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expired Subscriptions</div>
            <div class="stat-value">{{ $statusCounts['expired'] ?? 0 }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">{{ $statusCounts['cancelled'] ?? 0 }} cancelled</div>
        </div>
    </div>
</div>

<div class="tabs">
    <a href="{{ route('superadmin.subscription.subscriptions') }}" class="tab {{ request()->filled('status') ? '' : 'active' }}">All Subscriptions</a>
    @foreach(['active' => 'Active', 'expiring_soon' => 'Expiring Soon', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
        <a href="{{ route('superadmin.subscription.subscriptions', ['status' => $value]) }}" class="tab {{ request('status') === $value ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('superadmin.subscription.subscriptions') }}" class="filter-bar">
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <div class="filter-item" style="flex: 0 0 auto;">
                <select name="society" class="form-control">
                    <option value="">All Societies</option>
                    @foreach($societies as $s)
                        <option value="{{ $s->id }}" {{ (string) request('society') === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('superadmin.subscription.subscriptions') }}" class="btn btn-secondary btn-sm"><i class="fas fa-rotate"></i> Reset</a>
            </div>
        </form>

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
                                    <p>{{ $sub->subscription_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="prefix-tag">{{ $sub->plan->name ?? 'N/A' }}</span></td>
                        <td>{{ $sub->start_date ? $sub->start_date->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $sub->end_date ? $sub->end_date->format('d M Y') : 'N/A' }}</td>
                        <td><span class="status-badge {{ $sub->status }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span></td>
                        <td style="font-weight: 600;">&#8377; {{ number_format($sub->amount ?: ($sub->plan->amount ?? 0), 2) }}</td>
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
                                @if($sub->status !== 'cancelled')
                                    <a href="{{ route('superadmin.subscription.subscriptions.renew', $sub) }}" class="action-btn edit" title="Renew / Upgrade"><i class="fas fa-rotate"></i></a>
                                    <form method="POST" action="{{ route('superadmin.subscription.subscriptions.cancel', $sub) }}" onsubmit="return confirm('Cancel this subscription?');">
                                        @csrf
                                        <button type="submit" class="action-btn delete" title="Cancel"><i class="fas fa-ban"></i></button>
                                    </form>
                                @else
                                    <span style="font-size: 11px; color: var(--text-muted);">{{ $sub->cancel_reason ?: 'Cancelled' }}</span>
                                @endif
                                @if($sub->renewedFrom)
                                    <span class="badge badge-secondary" title="Renewed from {{ $sub->renewedFrom->subscription_number }}"><i class="fas fa-link"></i></span>
                                @endif
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
