@extends('society.layouts.app')

@section('title', 'Unit Management')

@php
    $pct = fn ($n) => $stats['total'] ? number_format(($n / $stats['total']) * 100, 2) : '0.00';
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Unit Management</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Unit Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Units</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('society.units.import') }}" class="btn btn-outline-secondary"><i class="fas fa-upload"></i> Import Units</a>
            <a href="{{ route('society.units.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Unit</a>
        </div>
    </div>
</div>

{{-- Stats + Summary --}}
<div style="display: grid; grid-template-columns: repeat(4, 1fr) 300px; gap: 16px; margin-bottom: 24px;" class="units-stats-grid">
    @include('society.partials.stat-card', ['icon' => 'fa-building', 'iconVariant' => 'peach', 'label' => 'Total Units', 'value' => number_format($stats['total']), 'trend' => 'Across all buildings', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-users', 'iconVariant' => 'success', 'label' => 'Occupied Units', 'value' => number_format($stats['occupied']), 'trend' => $pct($stats['occupied']).'% of total', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-house', 'iconVariant' => 'warning', 'label' => 'Vacant Units', 'value' => number_format($stats['vacant']), 'trend' => $pct($stats['vacant']).'% of total', 'trendType' => 'warning'])
    @include('society.partials.stat-card', ['icon' => 'fa-screwdriver-wrench', 'iconVariant' => 'purple', 'label' => 'Under Maintenance', 'value' => number_format($stats['under_maintenance']), 'trend' => $pct($stats['under_maintenance']).'% of total', 'trendType' => 'purple'])

    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 16px;">
            <div class="card-title" style="margin-bottom: 12px;">Unit Summary</div>
            <div style="display: flex; align-items: center; gap: 12px;">
                @include('society.partials.donut', [
                    'segments' => [
                        ['value' => $stats['occupied'], 'color' => '#10B981'],
                        ['value' => $stats['vacant'], 'color' => '#F59E0B'],
                        ['value' => $stats['under_maintenance'], 'color' => '#8B5CF6'],
                    ],
                    'centerValue' => number_format($stats['total']),
                    'centerLabel' => 'Total Units',
                    'size' => 110,
                    'stroke' => 14,
                ])
                <div style="flex: 1; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px;">
                        <span class="legend-dot" style="background: #10B981;"></span>
                        <span style="color: var(--text-secondary);">Occupied</span>
                        <span style="margin-left: auto; font-weight: 600;">{{ number_format($stats['occupied']) }} ({{ $pct($stats['occupied']) }}%)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px;">
                        <span class="legend-dot" style="background: #F59E0B;"></span>
                        <span style="color: var(--text-secondary);">Vacant</span>
                        <span style="margin-left: auto; font-weight: 600;">{{ number_format($stats['vacant']) }} ({{ $pct($stats['vacant']) }}%)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px;">
                        <span class="legend-dot" style="background: #8B5CF6;"></span>
                        <span style="color: var(--text-secondary);">Under Maintenance</span>
                        <span style="margin-left: auto; font-weight: 600;">{{ number_format($stats['under_maintenance']) }} ({{ $pct($stats['under_maintenance']) }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('society.units.index') }}">
    <div class="filter-bar" style="flex-direction: column; align-items: stretch;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="filter-item">
                <label>Building</label>
                <select name="building" class="form-control">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $b)<option value="{{ $b }}" {{ request('building') === $b ? 'selected' : '' }}>{{ $b }}</option>@endforeach
                </select>
            </div>
            <div class="filter-item">
                <label>Wing / Block</label>
                <select name="wing" class="form-control">
                    <option value="">All Wings</option>
                    @foreach($wings as $w)<option value="{{ $w }}" {{ request('wing') === $w ? 'selected' : '' }}>{{ $w }}</option>@endforeach
                </select>
            </div>
            <div class="filter-item">
                <label>Floor</label>
                <select name="floor" class="form-control">
                    <option value="">All Floors</option>
                    @foreach($floors as $f)<option value="{{ $f }}" {{ request('floor') === $f ? 'selected' : '' }}>{{ $f }}</option>@endforeach
                </select>
            </div>
            <div class="filter-item">
                <label>Unit Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    @foreach($types as $t)<option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                </select>
            </div>
            <div class="filter-item">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="vacant" {{ request('status') === 'vacant' ? 'selected' : '' }}>Vacant</option>
                    <option value="under_maintenance" {{ request('status') === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                </select>
            </div>
        </div>
        <div style="display: flex; gap: 12px; align-items: flex-end;">
            <div class="filter-item" style="flex: 1;">
                <label>Search Unit</label>
                <div class="header-search" style="max-width: none;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by flat no, unit no, owner name, mobile…">
                </div>
            </div>
            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.units.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle"><i class="fas fa-download"></i> Export <i class="fas fa-chevron-down" style="font-size: 9px;"></i></button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item"><i class="fas fa-file-csv"></i> CSV</a>
                    <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Excel</a>
                    <a href="#" class="dropdown-item"><i class="fas fa-file-pdf"></i> PDF</a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Units table --}}
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Unit No.</th>
                        <th>Building</th>
                        <th>Wing / Block</th>
                        <th>Floor</th>
                        <th>Unit Type</th>
                        <th>Area (sq.ft.)</th>
                        <th>Status</th>
                        <th>Occupied By</th>
                        <th>Owner / Member</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr class="{{ $unit->status === 'under_maintenance' ? 'row-maintenance' : '' }}">
                            <td style="padding-left: 20px; font-weight: 600;">{{ $unit->unit_number }}</td>
                            <td>{{ $unit->building }}</td>
                            <td>{{ $unit->wing }}</td>
                            <td>{{ $unit->floor }}</td>
                            <td>{{ $unit->unit_type }}</td>
                            <td>{{ $unit->area_sqft }}</td>
                            <td>@include('society.partials.status-badge', ['class' => $unit->statusBadgeClass(), 'label' => $unit->statusLabel()])</td>
                            <td>
                                @if($unit->occupied_by_name)
                                    <div style="font-size: 13px;">{{ $unit->occupied_by_name }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $unit->occupied_by_role }}</div>
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 13px;">{{ $unit->owner_name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $unit->owner_mobile }}</div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('society.units.show', $unit) }}" class="action-btn view"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.units.edit', $unit) }}" class="action-btn edit"><i class="fas fa-pencil"></i></a>
                                    <button class="action-btn"><i class="fas fa-ellipsis-vertical"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-building"></i></div>
                                    <div class="empty-state-title">No units found</div>
                                    <div class="empty-state-text">Try adjusting your filters or add a new unit.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $units, 'firstLast' => true, 'side' => 4, 'unit' => 'entries'])
@endsection
