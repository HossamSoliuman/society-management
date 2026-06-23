@extends('superadmin.layouts.app')

@section('title', $user->name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $user->name }}</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.users.index') }}">User Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $user->name }}</span>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-user" style="color: var(--primary); margin-right: 8px;"></i>User Information</div></div>
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=2563EB&color=fff&size=80" alt="" style="width: 80px; height: 80px; border-radius: 50%;">
                <div>
                    <div style="font-size: 18px; font-weight: 700;">{{ $user->name }}</div>
                    <div style="font-size: 13px; color: var(--text-secondary);">{{ $user->email }}</div>
                    <div style="margin-top: 8px;">
                        @foreach($user->roles as $role)
                            <span class="badge badge-primary">{{ $role->display_name }}</span>
                        @endforeach
                        <span class="status-badge {{ $user->status }}">{{ ucfirst($user->status) }}</span>
                    </div>
                </div>
            </div>
            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 16px 0;">
            <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $user->email }}</span></div>
            <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $user->mobile ?? 'N/A' }}</span></div>
            <div class="review-row"><span class="review-label">Status</span><span class="status-badge {{ $user->status }}">{{ ucfirst($user->status) }}</span></div>
            <div class="review-row"><span class="review-label">Created</span><span class="review-value">{{ $user->created_at->format('d M Y') }}</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-key" style="color: var(--warning); margin-right: 8px;"></i>Role & Permissions</div></div>
        <div class="card-body">
            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Assigned Roles</div>
                @foreach($user->roles as $role)
                    <span class="badge badge-primary" style="margin-right: 6px;">{{ $role->display_name }}</span>
                @endforeach
            </div>
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <span>This user has full access to all modules and features based on their assigned role.</span>
            </div>
        </div>
    </div>
</div>
@endsection
