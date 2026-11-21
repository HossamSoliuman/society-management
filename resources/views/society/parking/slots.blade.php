@extends('society.layouts.app')

@section('title', 'Parking Slots')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Parking Slots</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Parking Slots</span>
            </div>
        </div>
        <a href="{{ route('society.parking.allotments') }}" class="btn btn-secondary"><i class="fas fa-square-parking"></i> Allotments</a>
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

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-square-parking', 'iconVariant' => 'peach', 'label' => 'Total Slots', 'value' => number_format($stats['total'])])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Free', 'value' => number_format($stats['free'])])
    @include('society.partials.stat-card', ['icon' => 'fa-car', 'iconVariant' => 'info', 'label' => 'Allotted', 'value' => number_format($stats['allotted'])])
    @include('society.partials.stat-card', ['icon' => 'fa-ban', 'iconVariant' => 'danger', 'label' => 'Blocked', 'value' => number_format($stats['blocked'])])
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.parking.slots') }}" style="display: grid; grid-template-columns: 1fr 150px 150px 140px auto; gap: 12px; align-items: end; margin-bottom: 16px;">
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Slot number...">
                    </div>
                    <select name="area" class="form-control">
                        <option value="">All Areas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}" @selected(request('area') === $area)>{{ $area }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="state" class="form-control">
                        <option value="">Any state</option>
                        <option value="available" @selected($state === 'available')>Free</option>
                        <option value="allotted" @selected($state === 'allotted')>Allotted</option>
                        <option value="blocked" @selected($state === 'blocked')>Blocked</option>
                    </select>
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Slot</th>
                                <th>Area</th>
                                <th>Type</th>
                                <th>Allotted To</th>
                                <th>State</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slots as $slot)
                                @php
                                    $state = $slot->state();
                                    $allotment = $slot->activeAllotment;
                                @endphp
                                <tr>
                                    <td style="font-weight: 600;">{{ $slot->slot_number }}</td>
                                    <td>{{ $slot->area ?? '—' }}{{ $slot->is_covered ? '' : ' · Open' }}</td>
                                    <td>{{ $slot->typeLabel() }}</td>
                                    <td>
                                        @if($allotment)
                                            {{ $allotment->unit?->unit_number ?? '—' }}
                                            @if($allotment->vehicle)<div style="font-size: 11px; color: var(--text-muted);">{{ $allotment->vehicle->registration_no }}</div>@endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ ['available' => 'badge-success', 'allotted' => 'badge-info', 'blocked' => 'badge-danger'][$state] }}">{{ ['available' => 'Free', 'allotted' => 'Allotted', 'blocked' => 'Blocked'][$state] }}</span>
                                    </td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            @if($state !== 'allotted')
                                                <form method="POST" action="{{ route('society.parking.slots.toggle', $slot) }}">
                                                    @csrf
                                                    <button type="submit" class="action-btn edit" title="{{ $state === 'blocked' ? 'Reopen' : 'Block' }}"><i class="fas {{ $state === 'blocked' ? 'fa-lock-open' : 'fa-lock' }}"></i></button>
                                                </form>
                                                <form method="POST" action="{{ route('society.parking.slots.destroy', $slot) }}" data-confirm="Delete slot {{ $slot->slot_number }}?" data-confirm-label="Delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-square-parking"></i></div>
                                            <div class="empty-state-title">No slots yet</div>
                                            <div class="empty-state-text">Add slots one at a time or a whole run (e.g. B1-001 × 40).</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $slots, 'unit' => 'slots'])
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Add Slots</div>
                <form method="POST" action="{{ route('society.parking.slots.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Slot No. <span class="required">*</span></label>
                            <input type="text" name="slot_number" class="form-control" value="{{ old('slot_number') }}" placeholder="B1-001" required style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">How Many</label>
                            <input type="number" name="count" class="form-control" min="1" max="200" value="{{ old('count', 1) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Area</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area') }}" placeholder="Basement 1, Stilt, Open">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="slot_type" class="form-control">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('slot_type', 'car') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 14px;">
                        <input type="checkbox" name="is_covered" value="1" @checked(old('is_covered', true))> Covered
                    </label>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-plus"></i> Add</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
