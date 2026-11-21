@extends('society.layouts.app')

@section('title', 'Unit Owners')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Unit Owners</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Units</a>
                <span class="breadcrumb-separator">/</span>
                <span>Owners</span>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-user-tie', 'iconVariant' => 'peach', 'label' => 'Owners', 'value' => number_format($stats['owners'])])
    @include('society.partials.stat-card', ['icon' => 'fa-house-user', 'iconVariant' => 'success', 'label' => 'Self-occupied Units', 'value' => number_format($stats['self'])])
    @include('society.partials.stat-card', ['icon' => 'fa-key', 'iconVariant' => 'info', 'label' => 'Rented Out', 'value' => number_format($stats['rented'])])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-question', 'iconVariant' => 'warning', 'label' => 'Units Without Owner', 'value' => number_format($stats['missing'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.units.owners') }}" style="display: grid; grid-template-columns: 1fr 160px 190px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search unit, owner or mobile...">
            </div>
            <select name="wing" class="form-control">
                <option value="">All Wings</option>
                @foreach($wings as $wing)
                    <option value="{{ $wing }}" @selected(request('wing') === $wing)>{{ $wing }}</option>
                @endforeach
            </select>
            <select name="occupancy" class="form-control">
                <option value="">Any occupancy</option>
                @foreach($occupancyOptions as $value => $label)
                    <option value="{{ $value }}" @selected($occupancy === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.units.owners') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Owner</th>
                        <th>Contact</th>
                        <th>Occupancy</th>
                        <th>Outstanding Dues</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        @php
                            $member = $unit->member ?? ($ownerMembers[$unit->unit_number] ?? null);
                            $ownerName = $member?->name ?? $unit->owner_name;
                            $tenant = $tenants[$unit->id] ?? null;
                            $due = (float) ($dues[$unit->id] ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('society.units.show', $unit) }}" style="font-weight: 600;">{{ $unit->unit_number }}</a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ collect([$unit->wing, $unit->floor, $unit->unit_type])->filter()->implode(' · ') }}</div>
                            </td>
                            <td>
                                @if($member)
                                    <a href="{{ route('society.members.show', $member) }}">{{ $ownerName }}</a>
                                @else
                                    {{ $ownerName ?? '—' }}
                                @endif
                            </td>
                            <td>
                                <div>{{ $member?->mobile ?? $unit->owner_mobile ?? '—' }}</div>
                                @if($member?->email)<div style="font-size: 11px; color: var(--text-muted);">{{ $member->email }}</div>@endif
                            </td>
                            <td>
                                @if($unit->status === 'occupied' && $unit->occupied_by_role === 'Tenant')
                                    <span class="badge badge-info">Rented</span>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                        @if($tenant)
                                            <a href="{{ route('society.tenants.show', $tenant) }}">{{ $tenant->name }}</a>
                                        @else
                                            {{ $unit->occupied_by_name }}
                                        @endif
                                    </div>
                                @elseif($unit->status === 'occupied')
                                    <span class="badge badge-success">Self-occupied</span>
                                @else
                                    <span class="status-badge {{ $unit->statusBadgeClass() }}">{{ $unit->statusLabel() }}</span>
                                @endif
                            </td>
                            <td style="white-space: nowrap; {{ $due > 0 ? 'color: var(--danger); font-weight: 600;' : 'color: var(--text-muted);' }}">{{ $due > 0 ? format_inr($due, 0) : 'Nil' }}</td>
                            <td><a href="{{ route('society.units.show', $unit) }}" class="action-btn view" title="View unit"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-tie"></i></div>
                                    <div class="empty-state-title">No units match</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $units, 'unit' => 'units'])
    </div>
</div>
@endsection
