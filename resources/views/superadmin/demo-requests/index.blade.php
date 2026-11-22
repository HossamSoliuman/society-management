@extends('superadmin.layouts.app')

@section('title', 'Demo Requests')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Demo Requests</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-secondary"><i class="fas fa-arrow-up-right-from-square"></i> View website</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Demo Requests</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif

@php
    $statIcons = [
        'new' => ['orange', 'fa-inbox'],
        'contacted' => ['blue', 'fa-phone'],
        'converted' => ['green', 'fa-circle-check'],
        'closed' => ['gray', 'fa-box-archive'],
    ];
@endphp

<div class="stats-grid stats-grid-4">
    @foreach($statuses as $key => [$label, $badge])
        <a href="{{ route('superadmin.demo-requests.index', ['status' => $key]) }}" class="stat-card" style="text-decoration: none; color: inherit;">
            <div class="stat-icon {{ $statIcons[$key][0] }}"><i class="fas {{ $statIcons[$key][1] }}"></i></div>
            <div class="stat-info">
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value">{{ $counts[$key] ?? 0 }}</div>
            </div>
        </a>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('superadmin.demo-requests.index') }}" class="filter-bar">
            <div class="filter-item" style="flex: 1;">
                <div class="header-search" style="max-width: 100%;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, society, city, email or phone">
                </div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach($statuses as $key => [$label, $badge])
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto; display: flex; gap: 8px; align-self: flex-end;">
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('superadmin.demo-requests.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Flats</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demoRequests as $lead)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $lead->society_name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $lead->city }}</div>
                            </td>
                            <td>
                                <div>{{ $lead->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $lead->phone }} · {{ $lead->email }}</div>
                            </td>
                            <td>{{ $lead->roleLabel() }}</td>
                            <td>{{ $lead->units ?? '—' }}</td>
                            <td><span class="badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></td>
                            <td>{{ $lead->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <a href="{{ route('superadmin.demo-requests.show', $lead) }}" class="action-btn view" title="Open"><i class="fas fa-eye"></i></a>
                                    <form method="POST" action="{{ route('superadmin.demo-requests.destroy', $lead) }}" data-confirm="Delete the demo request from {{ $lead->society_name }}?" data-confirm-label="Delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                                    <div class="empty-state-title">No demo requests yet</div>
                                    <div class="empty-state-text">Requests sent from the website's "Request a demo" form land here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $demoRequests])
    </div>
</div>
@endsection
