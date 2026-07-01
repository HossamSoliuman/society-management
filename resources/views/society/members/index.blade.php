@extends('society.layouts.app')

@section('title', 'Member Management')

@php
    $activeStatus = request('status');
    $tabs = [
        '' => 'All Members',
        'active' => 'Active Members',
        'inactive' => 'Inactive Members',
        'blocked' => 'Blocked Members',
    ];
    $pct = fn ($n) => $stats['total'] ? number_format(($n / $stats['total']) * 100, 1) : '0';
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Member Management</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.members.index') }}">Member Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>All Members</span>
            </div>
        </div>
        <a href="{{ route('society.members.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Member
        </a>
    </div>
</div>

{{-- Tabs --}}
<div class="tabs">
    @foreach($tabs as $value => $label)
        <a href="{{ route('society.members.index', $value ? ['status' => $value] : []) }}"
           class="tab {{ (string) $activeStatus === (string) $value ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Stats --}}
<div class="stats-grid">
    @include('society.partials.stat-card', ['icon' => 'fa-users', 'iconVariant' => 'peach', 'label' => 'Total Members', 'value' => number_format($stats['total']), 'trend' => '8 this month', 'trendType' => 'up'])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Active Members', 'value' => number_format($stats['active']), 'trend' => $pct($stats['active']).'% of total', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-user-clock', 'iconVariant' => 'warning', 'label' => 'Inactive Members', 'value' => number_format($stats['inactive']), 'trend' => $pct($stats['inactive']).'% of total', 'trendType' => 'warning'])
    @include('society.partials.stat-card', ['icon' => 'fa-user-slash', 'iconVariant' => 'danger', 'label' => 'Blocked Members', 'value' => number_format($stats['blocked']), 'trend' => $pct($stats['blocked']).'% of total', 'trendType' => 'danger'])
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('society.members.index') }}">
    <div class="filter-bar">
        <div class="filter-item" style="flex: 2;">
            <label>Search Member</label>
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, mobile, flat no, email…">
            </div>
        </div>
        <div class="filter-item">
            <label>Tower / Wing</label>
            <select name="tower" class="form-control">
                <option value="">All Towers</option>
                @foreach($towers as $tower)
                    <option value="{{ $tower }}" {{ request('tower') === $tower ? 'selected' : '' }}>{{ $tower }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label>Flat / Unit</label>
            <select name="unit" class="form-control">
                <option value="">All Units</option>
                @foreach($units as $unit)
                    <option value="{{ $unit }}" {{ request('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>
        </div>
        <div class="filter-item">
            <label>Member Type</label>
            <select name="type" class="form-control">
                <option value="">All Types</option>
                <option value="owner" {{ request('type') === 'owner' ? 'selected' : '' }}>Owner</option>
                <option value="family_member" {{ request('type') === 'family_member' ? 'selected' : '' }}>Family Member</option>
                <option value="tenant" {{ request('type') === 'tenant' ? 'selected' : '' }}>Tenant</option>
            </select>
        </div>
        <div class="filter-item" style="flex: 0 0 auto; min-width: auto; display: flex; gap: 8px; align-items: flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.members.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </div>
    </div>
</form>

{{-- Table toolbar --}}
<div class="action-toolbar" style="justify-content: flex-end;">
    <div class="action-toolbar-right">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle"><i class="fas fa-download"></i> Export <i class="fas fa-chevron-down" style="font-size: 9px;"></i></button>
            <div class="dropdown-menu">
                <a href="#" class="dropdown-item"><i class="fas fa-file-csv"></i> CSV</a>
                <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Excel</a>
                <a href="#" class="dropdown-item"><i class="fas fa-file-pdf"></i> PDF</a>
            </div>
        </div>
        <button class="action-btn view" style="border-color: var(--primary); color: var(--primary);"><i class="fas fa-list"></i></button>
        <button class="action-btn"><i class="fas fa-table-cells-large"></i></button>
        <button class="action-btn"><i class="fas fa-gear"></i></button>
    </div>
</div>

{{-- Members table --}}
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">#</th>
                        <th>Member Details</th>
                        <th>Flat / Unit</th>
                        <th>Tower / Wing</th>
                        <th>Mobile / Email</th>
                        <th>Member Type</th>
                        <th>Status</th>
                        <th>Join Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td style="padding-left: 20px;">{{ $loop->iteration + ($members->firstItem() - 1) }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="avatar" style="width: 36px; height: 36px;">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=FEE8E0&color=E84B1E" alt="">
                                    </div>
                                    <div class="user-info">
                                        <span class="user-name">{{ $member->name }}</span>
                                        <span class="user-email">{{ $member->typeLabel() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $member->flat_unit }}</td>
                            <td>{{ $member->tower_wing }}</td>
                            <td>
                                <div style="font-size: 13px;">{{ $member->mobile }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $member->email }}</div>
                            </td>
                            <td>{{ $member->typeLabel() }}</td>
                            <td>@include('society.partials.status-badge', ['class' => $member->statusBadgeClass(), 'label' => ucfirst($member->status)])</td>
                            <td>{{ optional($member->join_date)->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('society.members.show', $member) }}" class="action-btn view"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.members.edit', $member) }}" class="action-btn edit"><i class="fas fa-pencil"></i></a>
                                    <button class="action-btn"><i class="fas fa-ellipsis-vertical"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                                    <div class="empty-state-title">No members found</div>
                                    <div class="empty-state-text">Try adjusting your filters or add a new member.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $members, 'side' => 2, 'unit' => 'members'])
@endsection
