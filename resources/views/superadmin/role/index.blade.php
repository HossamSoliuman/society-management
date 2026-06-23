@extends('superadmin.layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Role Management</h1>
            <p class="page-subtitle">Manage roles and their permissions.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Role</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.users.index') }}">User Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Role Management</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); max-width: 400px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user-tag"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Roles</div>
            <div class="stat-value">{{ $totalRoles }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Roles</div>
            <div class="stat-value">{{ $activeRoles }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Display Name</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $role->name }}</div>
                        </td>
                        <td>{{ $role->display_name }}</td>
                        <td style="font-size: 12px; color: var(--text-secondary); max-width: 250px;">{{ Str::limit($role->description, 60) ?: '—' }}</td>
                        <td><span class="badge badge-info">{{ $role->permissions_count }} permissions</span></td>
                        <td><span class="status-badge {{ $role->status }}">{{ ucfirst($role->status) }}</span></td>
                        <td style="font-size: 12px;">{{ $role->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('superadmin.roles.edit', $role) }}" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('superadmin.roles.destroy', $role) }}" method="POST" style="display: inline;" data-confirm="Delete this role? This will remove all permission assignments.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">No roles found. <a href="{{ route('superadmin.roles.create') }}">Create one.</a></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $roles])
    </div>
</div>
@endsection
