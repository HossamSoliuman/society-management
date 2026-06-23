@extends('superadmin.layouts.app')

@section('title', 'Society Report')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Society Report</h1>
            <p class="page-subtitle">Detailed report of all registered societies.</p>
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
        <span>Society Report</span>
    </div>
</div>

@php
    $total = $societies->count();
    $active = $societies->where('status', 'active')->count();
    $inactive = $societies->where('status', 'inactive')->count();
    $trial = $societies->where('status', 'trial')->count();
@endphp

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-building"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Societies</div>
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
        <div class="stat-icon orange"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Inactive</div>
            <div class="stat-value">{{ $inactive }}</div>
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
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by society name, city..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option><option>Active</option><option>Inactive</option><option>Trial</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Types</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society Name</th>
                        <th>Type</th>
                        <th>City</th>
                        <th>Units</th>
                        <th>Subscription Plan</th>
                        <th>Status</th>
                        <th>Join Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($societies as $society)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $society->name }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $society->registration_number }}</div>
                        </td>
                        <td>{{ $society->societyType->name ?? '—' }}</td>
                        <td>{{ $society->city ?? '—' }}</td>
                        <td>{{ ($society->flats_count ?? 0) + ($society->shops_count ?? 0) + ($society->offices_count ?? 0) }}</td>
                        <td>{{ $society->subscriptionPlan->name ?? '—' }}</td>
                        <td><span class="status-badge {{ $society->status }}">{{ ucfirst($society->status) }}</span></td>
                        <td style="font-size: 12px;">{{ $society->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">No societies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
