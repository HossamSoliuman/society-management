@extends('superadmin.layouts.app')

@section('title', $society->name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $society->name }}</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.societies.edit', $society) }}" class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.societies.index') }}">Society Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $society->name }}</span>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-info-circle" style="color: var(--primary); margin-right: 8px;"></i>Basic Information</div>
        </div>
        <div class="card-body">
            <div class="review-row">
                <span class="review-label">Society Name</span>
                <span class="review-value">{{ $society->name }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Registration Number</span>
                <span class="review-value">{{ $society->registration_number ?: 'N/A' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Society Type</span>
                <span class="review-value">{{ $society->societyType->name ?? 'N/A' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Society Prefix</span>
                <span class="prefix-tag">{{ $society->prefix }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">PAN Number</span>
                <span class="review-value">{{ $society->pan_number ?: 'N/A' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Total Units</span>
                <span class="review-value">{{ $society->total_units }} (Flats: {{ $society->flats_count }}, Shops: {{ $society->shops_count }}, Offices: {{ $society->offices_count }})</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-map-marker-alt" style="color: var(--danger); margin-right: 8px;"></i>Address</div>
        </div>
        <div class="card-body">
            <div class="review-row">
                <span class="review-label">Address</span>
                <span class="review-value">{{ $society->address_line_1 }}{{ $society->address_line_2 ? ', ' . $society->address_line_2 : '' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">City</span>
                <span class="review-value">{{ $society->city }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">State</span>
                <span class="review-value">{{ $society->state }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Pincode</span>
                <span class="review-value">{{ $society->pincode }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-user-shield" style="color: var(--primary); margin-right: 8px;"></i>Society Administrators</div>
        <a href="{{ route('superadmin.users.create', ['society_id' => $society->id]) }}" class="btn btn-secondary"><i class="fas fa-plus"></i> Add user</a>
    </div>
    <div class="card-body">
        @forelse($society->users as $user)
            <div class="review-row">
                <span class="review-label">
                    {{ $user->name }}
                    <small style="display:block; color:var(--text-muted); margin-top:3px;">{{ $user->email }}</small>
                </span>
                <span class="review-value" style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    @foreach($user->roles as $role)<span class="badge badge-primary">{{ $role->display_name }}</span>@endforeach
                    <span class="status-badge {{ $user->status }}">{{ ucfirst($user->status) }}</span>
                    @if($user->status === 'active')
                        <form method="POST" action="{{ route('superadmin.users.resend-invitation', $user) }}">
                            @csrf
                            <button class="btn btn-secondary" type="submit"><i class="fas fa-paper-plane"></i> Resend setup link</button>
                        </form>
                    @endif
                </span>
            </div>
        @empty
            <p style="color: var(--text-muted);">No users are linked to this society.</p>
        @endforelse
    </div>
</div>
@endsection
