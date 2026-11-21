@extends('society.layouts.app')

@section('title', 'Vehicles')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Vehicles</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Vehicles</span>
            </div>
        </div>
        <a href="{{ route('society.vehicles.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Register Vehicle</a>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-car', 'iconVariant' => 'peach', 'label' => 'Registered Vehicles', 'value' => number_format($stats['total'])])
    @include('society.partials.stat-card', ['icon' => 'fa-car-side', 'iconVariant' => 'info', 'label' => 'Cars', 'value' => number_format($stats['cars'])])
    @include('society.partials.stat-card', ['icon' => 'fa-motorcycle', 'iconVariant' => 'purple', 'label' => 'Two Wheelers', 'value' => number_format($stats['two_wheelers'])])
    @include('society.partials.stat-card', ['icon' => 'fa-square-parking', 'iconVariant' => 'warning', 'label' => 'Without Parking', 'value' => number_format($stats['unparked'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.vehicles.index') }}" style="display: grid; grid-template-columns: 1fr 160px 170px 140px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search number, owner, unit or tag...">
            </div>
            <select name="type" class="form-control">
                <option value="">All Types</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="parking" class="form-control">
                <option value="">Any parking</option>
                <option value="allotted" @selected(request('parking') === 'allotted')>Has a slot</option>
                <option value="none" @selected(request('parking') === 'none')>No slot</option>
            </select>
            <select name="status" class="form-control">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.vehicles.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Registration</th>
                        <th>Type</th>
                        <th>Make / Model</th>
                        <th>Owner</th>
                        <th>Unit</th>
                        <th>Parking</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            <td>
                                <span style="font-weight: 600;">{{ $vehicle->registration_no }}</span>
                                @if($vehicle->rfid_tag)<div style="font-size: 11px; color: var(--text-muted);">Tag {{ $vehicle->rfid_tag }}</div>@endif
                            </td>
                            <td>{{ $vehicle->typeLabel() }}</td>
                            <td>{{ trim($vehicle->make.' '.$vehicle->model) ?: '—' }}{{ $vehicle->color ? ' · '.$vehicle->color : '' }}</td>
                            <td>{{ $vehicle->member?->name ?? $vehicle->owner_name ?? '—' }}</td>
                            <td>{{ $vehicle->unit?->unit_number ?? '—' }}</td>
                            <td>
                                @if($vehicle->allotment)
                                    <span class="badge badge-success">{{ $vehicle->allotment->slot?->slot_number }}</span>
                                @else
                                    <span class="badge badge-secondary">None</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $vehicle->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($vehicle->status) }}</span></td>
                            <td><a href="{{ route('society.vehicles.edit', $vehicle) }}" class="action-btn edit" title="Edit"><i class="fas fa-pencil"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-car"></i></div>
                                    <div class="empty-state-title">No vehicles found</div>
                                    <div class="empty-state-text">Residents can also add their vehicles from the portal.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $vehicles, 'unit' => 'vehicles'])
    </div>
</div>
@endsection
