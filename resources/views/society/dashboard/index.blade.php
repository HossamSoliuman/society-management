@extends('society.layouts.app')

@section('title', 'Society Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Society Dashboard</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Dashboard</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span id="dashboardLoader" style="display: none; color: var(--text-muted); font-size: 12px;">
                <i class="fas fa-circle-notch fa-spin"></i> Updating…
            </span>
            <div class="header-search" style="max-width: 200px;">
                <i class="fas fa-calendar search-icon"></i>
                <select id="dashboardRange"
                        data-url="{{ route('society.dashboard.data') }}"
                        class="form-control"
                        style="padding-left: 36px; -webkit-appearance: none; appearance: none;">
                    @foreach($rangeOptions as $key => $label)
                        <option value="{{ $key }}" @selected($key === $range)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Row A — Stats --}}
<div class="stats-grid stats-grid-5" id="dash-stats">
    @include('society.partials.dashboard.stats', ['d' => $d])
</div>

{{-- Row B --}}
<div class="grid-3">
    <div class="card" id="dash-collection" style="display: flex; flex-direction: column;">
        @include('society.partials.dashboard.collection', ['d' => $d])
    </div>

    <div class="card" id="dash-revenue" style="display: flex; flex-direction: column;">
        @include('society.partials.dashboard.revenue', ['d' => $d])
    </div>

    {{-- Recent Activity --}}
    <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header"><div class="card-title">Recent Activity</div></div>
        <div class="card-body" style="flex: 1;">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @forelse($d['activities'] as $activity)
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div class="activity-icon {{ $activity['variant'] }}"><i class="fas {{ $activity['icon'] }}"></i></div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $activity['title'] }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $activity['subtitle'] }}</div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <div style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">{{ $activity['time'] }}</div>
                            @if($activity['amount'])
                                <div style="font-size: 13px; font-weight: 700; color: var(--success);">&#8377; {{ $activity['amount'] }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No recent activity.</div>
                @endforelse
            </div>
        </div>
        <div class="card-footer" style="padding: 12px 20px;">
            <a href="{{ route('society.placeholder', ['page' => 'Activity Logs']) }}" class="btn btn-outline-secondary" style="width: 100%;">View All Activity <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
        </div>
    </div>
</div>

{{-- Row C — 2fr / 1fr --}}
<div class="dashboard-row-c">
    <div>
        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="{{ route('society.members.create') }}" class="quick-action-item">
                        <i class="fas fa-user-plus" style="color: var(--primary);"></i><span>Add Member</span>
                    </a>
                    <a href="{{ route('society.units.create') }}" class="quick-action-item">
                        <i class="fas fa-building" style="color: var(--success);"></i><span>Add Unit</span>
                    </a>
                    <a href="{{ route('society.billing.bills.create') }}" class="quick-action-item">
                        <i class="fas fa-file-invoice" style="color: var(--primary);"></i><span>Create Invoice</span>
                    </a>
                    <a href="{{ route('society.collections.create') }}" class="quick-action-item">
                        <i class="fas fa-credit-card" style="color: var(--info);"></i><span>Record Payment</span>
                    </a>
                    <a href="{{ route('society.support.create') }}" class="quick-action-item">
                        <i class="fas fa-headset" style="color: var(--danger);"></i><span>Raise Complaint</span>
                    </a>
                    <a href="{{ route('society.placeholder', ['page' => 'Notifications']) }}" class="quick-action-item">
                        <i class="fas fa-paper-plane" style="color: var(--primary);"></i><span>Send Notice</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Complaint Summary + Occupancy Overview --}}
        <div class="grid-2">
            <div class="card" id="dash-complaint" style="display: flex; flex-direction: column;">
                @include('society.partials.dashboard.complaint', ['d' => $d])
            </div>

            <div class="card" id="dash-occupancy" style="display: flex; flex-direction: column;">
                @include('society.partials.dashboard.occupancy', ['d' => $d])
            </div>
        </div>
    </div>

    {{-- Notice Board --}}
    <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header">
            <div class="card-title">Notice Board</div>
            <a href="{{ route('society.notices.index') }}" class="btn-link">View All</a>
        </div>
        <div class="card-body" style="flex: 1;">
            <div style="display: flex; flex-direction: column; gap: 18px;">
                @forelse($d['notices'] as $notice)
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div class="activity-icon {{ $notice['variant'] }}"><i class="fas {{ $notice['icon'] }}"></i></div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; gap: 8px;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $notice['title'] }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">{{ $notice['date'] }}</div>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $notice['desc'] }}</div>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No notices published.</div>
                @endforelse
            </div>
        </div>
        <div class="card-footer" style="padding: 12px 20px;">
            <a href="{{ route('society.notices.index') }}" class="btn btn-outline-primary" style="width: 100%;">View All Notices <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
        </div>
    </div>
</div>

{{-- Row D — Society info strip --}}
<div class="card">
    <div class="info-strip">
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-building"></i></div>
            <div><div class="info-strip-label">Society Name</div><div class="info-strip-value">{{ $d['societyInfo']['name'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-location-dot"></i></div>
            <div><div class="info-strip-label">Address</div><div class="info-strip-value">{{ $d['societyInfo']['address'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-calendar"></i></div>
            <div><div class="info-strip-label">Established</div><div class="info-strip-value">{{ $d['societyInfo']['established'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-city"></i></div>
            <div><div class="info-strip-label">Total Towers</div><div class="info-strip-value">{{ $d['societyInfo']['towers'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-layer-group"></i></div>
            <div><div class="info-strip-label">Total Flats</div><div class="info-strip-value">{{ $d['societyInfo']['floors'] }}</div></div>
        </div>
    </div>
</div>
@endsection
