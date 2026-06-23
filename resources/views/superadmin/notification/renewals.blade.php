@extends('superadmin.layouts.app')

@section('title', 'Renewal Alerts')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Renewal Alerts</h1>
            <p class="page-subtitle">Manage renewal alerts and send reminders.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-primary"><i class="fas fa-bell"></i> Send Reminders</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Renewal Alerts</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-sync-alt"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Renewals Due</div>
            <div class="stat-value">{{ $totalRenewals }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <div class="stat-label">Due in 7 Days</div>
            <div class="stat-value">{{ $dueIn7Days }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-info">
            <div class="stat-label">Due in 30 Days</div>
            <div class="stat-value">{{ $dueIn30Days }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <div class="stat-label">Due in 60 Days</div>
            <div class="stat-value">{{ $dueIn60Days }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Overdue</div>
            <div class="stat-value">{{ $overdue }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by society, plan..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>Due Period</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Plans</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>Building</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days Left</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($renewals as $sub)
                    <tr>
                        <td>{{ $sub->society->name ?? 'N/A' }}</td>
                        <td>{{ $sub->building_name ?? 'All Buildings' }}</td>
                        <td><span class="prefix-tag">{{ $sub->plan->name ?? 'N/A' }}</span></td>
                        <td>{{ $sub->start_date ? $sub->start_date->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $sub->end_date ? $sub->end_date->format('d M Y') : 'N/A' }}</td>
                        <td>
                            @if($sub->end_date)
                                @php $daysLeft = now()->diffInDays($sub->end_date, false); @endphp
                                <span style="color: {{ $daysLeft <= 7 ? 'var(--danger)' : ($daysLeft <= 30 ? 'var(--warning)' : 'var(--success)') }}; font-weight: 600;">
                                    {{ $daysLeft > 0 ? $daysLeft . ' days' : 'Expired' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td><span class="status-badge {{ $sub->status }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span></td>
                        <td><div style="display: flex; gap: 4px;"><button class="action-btn view"><i class="fas fa-eye"></i></button><button class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Renew</button></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $renewals])
    </div>
</div>
@endsection
