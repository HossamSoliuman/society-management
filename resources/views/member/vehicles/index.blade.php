@extends('member.layouts.app')

@section('title', 'My Vehicles')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Vehicles</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Vehicles</span>
            </div>
        </div>
        <a href="{{ route('member.vehicles.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Vehicle</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Registration</th>
                        <th>Type</th>
                        <th>Make / Model</th>
                        <th>Colour</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            <td style="padding-left: 20px; font-weight: 600;">{{ $vehicle->registration_no }}</td>
                            <td>{{ $vehicle->typeLabel() }}</td>
                            <td>{{ trim($vehicle->make.' '.$vehicle->model) ?: '—' }}</td>
                            <td>{{ $vehicle->color ?: '—' }}</td>
                            <td>{{ $vehicle->unit?->unit_number ?? '—' }}</td>
                            <td><span class="badge {{ $vehicle->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($vehicle->status) }}</span></td>
                            <td style="text-align: right; padding-right: 20px;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('member.vehicles.edit', $vehicle) }}" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil"></i></a>
                                    <form method="POST" action="{{ route('member.vehicles.destroy', $vehicle) }}" data-confirm="Remove {{ $vehicle->registration_no }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">No vehicles registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
