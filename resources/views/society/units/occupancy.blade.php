@extends('society.layouts.app')

@section('title', 'Occupancy Status')

@push('styles')
<style>
    .occ-legend { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12px; color: var(--text-secondary); }
    .occ-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .occ-dot { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }
    .occ-owner { background: #10B981; }
    .occ-tenant { background: #3B82F6; }
    .occ-vacant { background: #F59E0B; }
    .occ-maintenance { background: #8B5CF6; }
    .occ-floor { display: grid; grid-template-columns: 90px 1fr; gap: 12px; align-items: start; padding: 8px 0; border-bottom: 1px dashed var(--border-color); }
    .occ-floor:last-child { border-bottom: none; }
    .occ-floor-label { font-size: 12px; color: var(--text-muted); padding-top: 8px; }
    .occ-tiles { display: flex; flex-wrap: wrap; gap: 8px; }
    .occ-tile { text-decoration: none; min-width: 74px; padding: 7px 10px; border-radius: 6px; color: #fff; font-size: 12px; font-weight: 600; text-align: center; line-height: 1.25; }
    .occ-tile small { display: block; font-weight: 400; font-size: 10px; opacity: 0.9; max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .occ-tile:hover { filter: brightness(0.92); color: #fff; }
    .occ-tile.is-dim { opacity: 0.22; }
    @media (max-width: 768px) { .occ-floor { grid-template-columns: 1fr; gap: 4px; } .occ-floor-label { padding-top: 0; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Occupancy Status</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Units</a>
                <span class="breadcrumb-separator">/</span>
                <span>Occupancy</span>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid">
    @include('society.partials.stat-card', ['icon' => 'fa-building', 'iconVariant' => 'peach', 'label' => 'Total Units', 'value' => number_format($stats['total'])])
    @include('society.partials.stat-card', ['icon' => 'fa-house-circle-check', 'iconVariant' => 'success', 'label' => 'Occupied', 'value' => number_format($stats['occupied']), 'trend' => $stats['rate'].'% occupancy', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-key', 'iconVariant' => 'info', 'label' => 'Tenant-occupied', 'value' => number_format($stats['tenant'])])
    @include('society.partials.stat-card', ['icon' => 'fa-door-open', 'iconVariant' => 'warning', 'label' => 'Vacant', 'value' => number_format($stats['vacant'])])
    @include('society.partials.stat-card', ['icon' => 'fa-screwdriver-wrench', 'iconVariant' => 'purple', 'label' => 'Under Maintenance', 'value' => number_format($stats['maintenance'])])
</div>

<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 16px;">By Wing</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Wing</th>
                        <th>Units</th>
                        <th>Owner-occupied</th>
                        <th>Tenant-occupied</th>
                        <th>Vacant</th>
                        <th>Under Maintenance</th>
                        <th>Occupancy</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wingsSummary as $wing => $row)
                        @php $rate = $row['total'] > 0 ? round(($row['owner'] + $row['tenant']) / $row['total'] * 100) : 0; @endphp
                        <tr>
                            <td style="font-weight: 600;">{{ $wing }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>{{ $row['owner'] }}</td>
                            <td>{{ $row['tenant'] }}</td>
                            <td>{{ $row['vacant'] }}</td>
                            <td>{{ $row['maintenance'] }}</td>
                            <td style="min-width: 160px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 8px; background: var(--gray-100); border-radius: 4px; overflow: hidden;">
                                        <div style="width: {{ $rate }}%; height: 100%; background: var(--success);"></div>
                                    </div>
                                    <span style="font-size: 12px; font-weight: 600;">{{ $rate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.units.occupancy') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div class="occ-legend">
                <span><i class="occ-dot occ-owner"></i> Owner-occupied</span>
                <span><i class="occ-dot occ-tenant"></i> Tenant-occupied</span>
                <span><i class="occ-dot occ-vacant"></i> Vacant</span>
                <span><i class="occ-dot occ-maintenance"></i> Under maintenance</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <select name="wing" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Wings</option>
                    @foreach($wings as $wing)
                        <option value="{{ $wing }}" @selected(request('wing') === $wing)>{{ $wing }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    <option value="">Highlight all</option>
                    <option value="occupied" @selected($status === 'occupied')>Occupied</option>
                    <option value="vacant" @selected($status === 'vacant')>Vacant</option>
                    <option value="under_maintenance" @selected($status === 'under_maintenance')>Under maintenance</option>
                </select>
            </div>
        </form>

        @foreach($grid as $wing => $floors)
            <div class="section-title" style="font-size: 15px; margin-top: 12px;">{{ $wing }}</div>
            @foreach($floors as $floor => $floorUnits)
                <div class="occ-floor">
                    <div class="occ-floor-label">{{ $floor }}</div>
                    <div class="occ-tiles">
                        @foreach($floorUnits as $unit)
                            @php
                                $kind = match (true) {
                                    $unit->status === 'vacant' => 'occ-vacant',
                                    $unit->status === 'under_maintenance' => 'occ-maintenance',
                                    $unit->occupied_by_role === 'Tenant' => 'occ-tenant',
                                    default => 'occ-owner',
                                };
                            @endphp
                            <a href="{{ route('society.units.show', $unit) }}" class="occ-tile {{ $kind }} {{ $status && $unit->status !== $status ? 'is-dim' : '' }}" title="{{ $unit->unit_number }} · {{ $unit->statusLabel() }}{{ $unit->occupied_by_name ? ' · '.$unit->occupied_by_name : '' }}">
                                {{ $unit->unit_number }}
                                <small>{{ $unit->status === 'occupied' ? ($unit->occupied_by_name ?? $unit->owner_name ?? '') : $unit->statusLabel() }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
@endsection
