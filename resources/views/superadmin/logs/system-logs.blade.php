@extends('superadmin.layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">System Logs</h1>
            <p class="page-subtitle">View system logs and monitor platform health.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="btn btn-outline-danger"><i class="fas fa-trash"></i> Clear Logs</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>System Logs</span>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.logs.user-activities') }}" class="sub-nav-tab">User Activities</a>
    <a href="{{ route('superadmin.logs.system-logs') }}" class="sub-nav-tab active">System Logs</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by message, module, user..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Levels</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Modules</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
        </div>

        <div style="margin-bottom: 16px;"><div class="section-title" style="font-size: 14px; margin: 0;"><i class="fas fa-list"></i> System Logs</div></div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Module</th>
                        <th>User</th>
                        <th>Message</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td><span class="log-level {{ strtolower($log->level) }}">{{ strtoupper($log->level) }}</span></td>
                        <td>{{ $log->module }}</td>
                        <td>
                            <div class="user-name">{{ $log->user_name ?? 'System' }}</div>
                            @if($log->user_email)<div class="user-email">{{ $log->user_email }}</div>@endif
                        </td>
                        <td style="font-size: 12px; max-width: 350px;">{{ Str::limit($log->message, 100) }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $log->ip_address }}</td>
                        <td style="font-size: 12px;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $logs])
    </div>
    <div class="card-footer"><i class="fas fa-info-circle" style="margin-right: 6px;"></i> View system logs and monitor platform health.</div>
</div>
@endsection
