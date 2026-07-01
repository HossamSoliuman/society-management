@extends('society.layouts.app')

@section('title', 'Unit Details')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Unit {{ $unit->unit_number }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Unit Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $unit->unit_number }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('society.units.edit', $unit) }}" class="btn btn-primary"><i class="fas fa-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('society.units.destroy', $unit) }}" data-confirm="Delete this unit?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
            <div class="building-icon" style="width: 56px; height: 56px; font-size: 22px;"><i class="fas fa-door-closed"></i></div>
            <div>
                <h2 style="font-size: 18px; font-weight: 700;">{{ $unit->unit_number }}</h2>
                <div style="margin-top: 4px;">@include('society.partials.status-badge', ['class' => $unit->statusBadgeClass(), 'label' => $unit->statusLabel()])</div>
            </div>
        </div>

        @php
            $rows = [
                ['fa-building', 'Building', $unit->building],
                ['fa-layer-group', 'Wing / Block', $unit->wing],
                ['fa-stairs', 'Floor', $unit->floor],
                ['fa-house', 'Unit Type', $unit->unit_type],
                ['fa-ruler-combined', 'Area (sq.ft.)', $unit->area_sqft],
                ['fa-user', 'Occupied By', $unit->occupied_by_name ? $unit->occupied_by_name.' ('.$unit->occupied_by_role.')' : '—'],
                ['fa-user-tie', 'Owner', $unit->owner_name],
                ['fa-phone', 'Owner Mobile', $unit->owner_mobile],
            ];
        @endphp
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px 32px;">
            @foreach($rows as [$icon, $label, $value])
                <div class="detail-row">
                    <div class="detail-row-icon"><i class="fas {{ $icon }}"></i></div>
                    <div class="detail-row-label">{{ $label }}</div>
                    <div class="detail-row-value">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
