@extends('superadmin.layouts.app')

@section('title', 'Login Activity')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Login Activity</h1>
            <p class="page-subtitle">Monitor all user login activity across the platform.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.users.index') }}">User Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Login Activity</span>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.users.index') }}" class="sub-nav-tab">Society Admin Users</a>
    <a href="{{ route('superadmin.roles.index') }}" class="sub-nav-tab">Role Management</a>
    <a href="{{ route('superadmin.users.login-activity') }}" class="sub-nav-tab active">Login Activity</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by user, IP address..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option><option>Success</option><option>Failed</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" placeholder="Date range..." readonly></div></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $log)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="background: {{ ['#E84B1E', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'][($loop->index % 5)] }}; width: 32px; height: 32px; font-size: 12px;">{{ collect(explode(' ', $log->user_name ?? 'U'))->map(fn($n) => $n[0] ?? '')->join('') }}</div>
                                <div class="user-info">
                                    <div class="user-name">{{ $log->user_name ?? '—' }}</div>
                                    <div class="user-email">{{ $log->user_email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-info">{{ $log->action }}</span></td>
                        <td style="font-size: 12px; max-width: 300px;">{{ Str::limit($log->description, 80) }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                        <td><span class="status-badge {{ $log->status ?? 'success' }}">{{ ucfirst($log->status ?? 'Success') }}</span></td>
                        <td style="font-size: 12px;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">No login activity records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $activities])
    </div>
</div>
@endsection
