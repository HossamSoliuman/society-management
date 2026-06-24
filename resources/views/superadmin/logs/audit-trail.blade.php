@extends('superadmin.layouts.app')

@section('title', 'Audit Trail')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Audit Trail</h1>
            <p class="page-subtitle">Complete record of all system changes and administrative actions.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Activity Logs</span>
        <span class="breadcrumb-separator">/</span>
        <span>Audit Trail</span>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.logs.user-activities') }}" class="sub-nav-tab">User Activities</a>
    <a href="{{ route('superadmin.logs.system-logs') }}" class="sub-nav-tab">System Logs</a>
    <a href="{{ route('superadmin.logs.audit-trail') }}" class="sub-nav-tab active">Audit Trail</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by user, action, module..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Modules</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Actions</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" placeholder="Date range..." readonly></div></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="background: {{ ['#E84B1E', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'][($loop->index % 5)] }}; width: 32px; height: 32px; font-size: 12px;">{{ collect(explode(' ', $log->user_name ?? 'S'))->map(fn($n) => $n[0] ?? '')->join('') }}</div>
                                <div class="user-info">
                                    <div class="user-name">{{ $log->user_name ?? 'System' }}</div>
                                    <div class="user-email">{{ $log->user_email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $actionClass = match($log->action ?? '') {
                                    'create', 'store' => 'badge-success',
                                    'update', 'edit' => 'badge-warning',
                                    'delete', 'destroy' => 'badge-danger',
                                    default => 'badge-info',
                                };
                            @endphp
                            <span class="badge {{ $actionClass }}">{{ $log->action }}</span>
                        </td>
                        <td>{{ $log->module ?? '—' }}</td>
                        <td style="font-size: 12px; max-width: 300px;">{{ Str::limit($log->description, 80) }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                        <td style="font-size: 12px;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">No audit trail records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $logs])
    </div>
    <div class="card-footer"><i class="fas fa-info-circle" style="margin-right: 6px;"></i> Audit trail is driven by the Activity Log model. All create/update/delete actions are captured here.</div>
</div>
@endsection
