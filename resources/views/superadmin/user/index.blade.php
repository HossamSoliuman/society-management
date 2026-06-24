@extends('superadmin.layouts.app')

@section('title', 'Society Admin Users')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Society Admin Users</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New User</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>User Management</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 6.7% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Users</div>
            <div class="stat-value">{{ $activeUsers }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 6.7% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-user-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Inactive Users</div>
            <div class="stat-value">{{ $inactiveUsers }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 2 from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-user-lock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Suspended Users</div>
            <div class="stat-value">{{ $suspendedUsers }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Same as last month</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by name, email, mobile, role..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Roles</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="form-check-input"></th>
                        <th>User</th>
                        <th>Contact Details</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ ['E84B1E','10B981','F59E0B','8B5CF6','EC4899'][($user->id % 5)] }}&color=fff&size=40" alt="" style="width: 40px; height: 40px; border-radius: 50%;">
                                <div class="user-info">
                                    <div class="user-name">{{ $user->name }}</div>
                                    <div class="user-email">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12px;">
                                <div><i class="fas fa-envelope" style="width: 14px; color: var(--text-muted);"></i> {{ $user->email }}</div>
                                <div style="color: var(--text-muted); margin-top: 2px;"><i class="fas fa-phone" style="width: 14px;"></i> {{ $user->mobile ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge {{ ['badge-primary', 'badge-success', 'badge-warning', 'badge-info'][$loop->index % 4] }}">{{ $role->display_name }}</span>
                            @endforeach
                        </td>
                        <td><span class="status-badge {{ $user->status }}">{{ ucfirst($user->status) }}</span></td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('superadmin.users.show', $user) }}" class="action-btn view"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('superadmin.users.edit', $user) }}" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" style="display: inline;" data-confirm="Delete this user?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $users])
    </div>
</div>
@endsection
