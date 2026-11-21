@extends('society.layouts.app')

@section('title', 'Parking Allotments')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Parking Allotments</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.parking.slots') }}">Parking</a>
                <span class="breadcrumb-separator">/</span>
                <span>Allotments</span>
            </div>
        </div>
        <a href="{{ route('society.parking.slots') }}" class="btn btn-secondary"><i class="fas fa-list"></i> Slots</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
                    <div class="tabs" style="margin-bottom: 0;">
                        <a href="{{ route('society.parking.allotments') }}" class="tab {{ $status === 'active' ? 'active' : '' }}">Current</a>
                        <a href="{{ route('society.parking.allotments', ['status' => 'ended']) }}" class="tab {{ $status === 'ended' ? 'active' : '' }}">Ended</a>
                    </div>
                    <div style="font-size: 13px; color: var(--text-secondary);">Monthly parking charges: <strong>{{ format_inr($monthlyIncome, 0) }}</strong></div>
                </div>

                <form method="GET" action="{{ route('society.parking.allotments') }}" style="margin-bottom: 12px;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search slot, unit or vehicle...">
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Slot</th>
                                <th>Unit</th>
                                <th>Vehicle</th>
                                <th>Since</th>
                                <th>Charge / Month</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allotments as $allotment)
                                <tr>
                                    <td style="font-weight: 600;">{{ $allotment->slot?->slot_number ?? '—' }}<div style="font-size: 11px; color: var(--text-muted); font-weight: 400;">{{ $allotment->slot?->area }}</div></td>
                                    <td>{{ $allotment->unit?->unit_number ?? '—' }}<div style="font-size: 11px; color: var(--text-muted);">{{ $allotment->member?->name }}</div></td>
                                    <td>{{ $allotment->vehicle?->registration_no ?? '—' }}</td>
                                    <td style="white-space: nowrap;">
                                        {{ $allotment->start_date->format('d M Y') }}
                                        @if($allotment->end_date)<div style="font-size: 11px; color: var(--text-muted);">to {{ $allotment->end_date->format('d M Y') }}</div>@endif
                                    </td>
                                    <td>{{ (float) $allotment->monthly_charge > 0 ? format_inr($allotment->monthly_charge, 0) : 'Free' }}</td>
                                    <td>
                                        @if($allotment->status === 'active')
                                            <form method="POST" action="{{ route('society.parking.allotments.end', $allotment) }}" data-confirm="End the allotment of slot {{ $allotment->slot?->slot_number }}? The slot becomes free." data-confirm-label="End Allotment">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-circle-stop"></i> End</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-square-parking"></i></div>
                                            <div class="empty-state-title">No allotments</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $allotments, 'unit' => 'allotments'])
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Allot a Slot</div>
                @if($freeSlots->isEmpty())
                    <div style="font-size: 13px; color: var(--text-muted);">No free slots. <a href="{{ route('society.parking.slots') }}">Add slots</a> first.</div>
                @else
                    <form method="POST" action="{{ route('society.parking.allotments.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Slot <span class="required">*</span></label>
                            <select name="parking_slot_id" class="form-control" required>
                                <option value="">Select a free slot</option>
                                @foreach($freeSlots as $slot)
                                    <option value="{{ $slot->id }}" @selected((string) old('parking_slot_id') === (string) $slot->id)>{{ $slot->slot_number }} · {{ $slot->typeLabel() }}{{ $slot->area ? ' · '.$slot->area : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit <span class="required">*</span></label>
                            <select name="unit_id" class="form-control" required>
                                <option value="">Select unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>{{ $unit->unit_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Vehicle</label>
                            <select name="vehicle_id" class="form-control">
                                <option value="">Not tied to a vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>{{ $vehicle->registration_no }} · {{ $vehicle->typeLabel() }}{{ $vehicle->unit ? ' · '.$vehicle->unit->unit_number : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">From <span class="required">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">₹ / Month</label>
                                <input type="number" step="0.01" min="0" name="monthly_charge" class="form-control" value="{{ old('monthly_charge', 0) }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-square-parking"></i> Allot</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
