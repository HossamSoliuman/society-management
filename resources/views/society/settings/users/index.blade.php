@extends('society.layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Roles &amp; Permissions</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Settings</span>
                <span class="breadcrumb-separator">/</span>
                <span>Roles &amp; Permissions</span>
            </div>
        </div>
        <a href="{{ route('society.settings.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Invite User</a>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Team Members</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Can sign in to this panel</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>Login enabled</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-user-slash"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Login blocked</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-user-shield"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Admins</div>
                    <div class="stat-value">{{ $stats['admins'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Full society access</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.settings.users.index') }}">
                    <div style="display: grid; grid-template-columns: 1fr 200px 200px auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or email...">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="role" class="form-control">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.settings.users.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php($role = $user->roles->first())
                                <tr>
                                    <td>{{ $users->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span style="font-weight: 600; display: block;">{{ $user->name }}</span>
                                        <span style="font-size: 11px; color: var(--text-muted);">{{ $user->email }}</span>
                                    </td>
                                    <td>{{ $user->mobile ?? '—' }}</td>
                                    <td><span class="badge badge-info">{{ $role?->display_name ?? '—' }}</span></td>
                                    <td>
                                        <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($user->status) }}</span>
                                    </td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.settings.users.edit', $user) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                            @unless($user->is(auth()->user()))
                                                <form method="POST" action="{{ route('society.settings.users.status', $user) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="action-btn" title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas {{ $user->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                            @if($user->status === 'active')
                                                <form method="POST" action="{{ route('society.settings.users.resend-invitation', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="action-btn" title="Resend invitation"><i class="fas fa-paper-plane"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                                            <div class="empty-state-title">No team members yet</div>
                                            <div class="empty-state-text">Invite a manager, accountant or staff member to get started.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $users, 'firstLast' => true, 'side' => 2, 'unit' => 'users'])
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Permission Matrix</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Module</th>
                                @foreach($permissionMatrix['roles'] as $role)
                                    <th style="text-align: center;">{{ $role->display_name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionMatrix['permissions'] as $permission)
                                <tr>
                                    <td>{{ $permission->display_name }}</td>
                                    @foreach($permissionMatrix['roles'] as $role)
                                        <td style="text-align: center;">
                                            @if($role->permissions->contains('name', $permission->name))
                                                <i class="fas fa-check" style="color: var(--success);"></i>
                                            @else
                                                <i class="fas fa-minus" style="color: var(--text-muted);"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Roles</div>
                @foreach($roles as $role)
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: var(--primary-light); color: var(--primary);"><i class="fas fa-user-tag"></i></span>
                        <span class="rli-main">
                            <span class="rli-title">{{ $role->display_name }}</span>
                            <span class="rli-sub">{{ $role->description }}</span>
                        </span>
                        <span class="rli-meta">{{ $role->permissions->count() }} perms</span>
                    </div>
                @endforeach
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-user-plus', 'label' => 'Invite User', 'desc' => 'Send a password setup email', 'url' => route('society.settings.users.create'), 'color' => 'orange'],
            ['icon' => 'fa-building', 'label' => 'Society Profile', 'desc' => 'Update society details', 'url' => route('society.profile'), 'color' => 'blue'],
        ]])

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Invited users receive an email link to set their password. Deactivated users cannot sign in.</span>
        </div>
    </div>
</div>
@endsection
