@extends('society.layouts.app')

@section('title', 'Member Details')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Member Details</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.members.index') }}">Member Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $member->name }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <form method="POST" action="{{ route('society.members.invite', $member) }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary" {{ $member->email ? '' : 'disabled title=Add an email address first' }}>
                    <i class="fas fa-paper-plane"></i> {{ $member->hasPortalAccess() ? 'Resend portal invite' : 'Invite to portal' }}
                </button>
            </form>
            <a href="{{ route('society.members.edit', $member) }}" class="btn btn-primary"><i class="fas fa-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('society.members.destroy', $member) }}" data-confirm="Delete this member?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
            <div class="avatar" style="width: 64px; height: 64px;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=FEE8E0&color=E84B1E&size=128" alt="">
            </div>
            <div>
                <h2 style="font-size: 18px; font-weight: 700;">{{ $member->name }}</h2>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                    <span style="font-size: 13px; color: var(--text-muted);">{{ $member->typeLabel() }}</span>
                    @include('society.partials.status-badge', ['class' => $member->statusBadgeClass(), 'label' => ucfirst($member->status)])
                </div>
            </div>
        </div>

        @php
            $rows = [
                ['fa-door-open', 'Flat / Unit', $member->flat_unit],
                ['fa-building', 'Tower / Wing', $member->tower_wing],
                ['fa-phone', 'Mobile', $member->mobile],
                ['fa-envelope', 'Email', $member->email],
                ['fa-user-tag', 'Member Type', $member->typeLabel()],
                ['fa-calendar', 'Join Date', optional($member->join_date)->format('d M Y')],
                ['fa-mobile-screen', 'Portal Access', $member->hasPortalAccess()
                    ? 'Invited '.optional($member->invited_at)->format('d M Y').' · login '.($member->user?->status ?? '—')
                    : 'Not invited'],
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

<div class="content-grid" style="grid-template-columns: 1fr 1fr; margin-top: 20px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Family Members ({{ $member->familyMembers->count() }})</h3></div>
        <div class="card-body">
            @forelse($member->familyMembers as $family)
                <div class="detail-row">
                    <div class="detail-row-icon"><i class="fas fa-user"></i></div>
                    <div class="detail-row-label">{{ $family->name }}</div>
                    <div class="detail-row-value">{{ $family->relation ?? '—' }}{{ $family->mobile ? ' · '.$family->mobile : '' }}</div>
                </div>
            @empty
                <div style="font-size: 13px; color: var(--text-muted);">No family members recorded.</div>
            @endforelse
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Vehicles ({{ $member->vehicles->count() }})</h3></div>
        <div class="card-body">
            @forelse($member->vehicles as $vehicle)
                <div class="detail-row">
                    <div class="detail-row-icon"><i class="fas fa-car"></i></div>
                    <div class="detail-row-label">{{ $vehicle->registration_no }}</div>
                    <div class="detail-row-value">{{ $vehicle->typeLabel() }}{{ $vehicle->make ? ' · '.$vehicle->make.' '.$vehicle->model : '' }}</div>
                </div>
            @empty
                <div style="font-size: 13px; color: var(--text-muted);">No vehicles recorded.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
