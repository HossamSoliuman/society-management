@extends('society.layouts.app')

@section('title', 'Activity Logs')

@section('content')
@php
    $actionBadges = [
        'created' => 'badge-success', 'updated' => 'badge-info', 'deleted' => 'badge-danger',
        'login' => 'badge-secondary', 'logout' => 'badge-secondary', 'login_failed' => 'badge-danger',
    ];
@endphp
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Activity Logs</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Activity Logs</span>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-clock-rotate-left', 'iconVariant' => 'peach', 'label' => 'Events Today', 'value' => number_format($stats['today'])])
    @include('society.partials.stat-card', ['icon' => 'fa-calendar-week', 'iconVariant' => 'info', 'label' => 'Last 7 Days', 'value' => number_format($stats['week'])])
    @include('society.partials.stat-card', ['icon' => 'fa-users', 'iconVariant' => 'success', 'label' => 'Active Users (7 days)', 'value' => number_format($stats['users'])])
    @include('society.partials.stat-card', ['icon' => 'fa-triangle-exclamation', 'iconVariant' => 'danger', 'label' => 'Failed (30 days)', 'value' => number_format($stats['failed'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.activity-logs.index') }}" style="display: grid; grid-template-columns: 1fr 150px 140px 170px 150px 150px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search descriptions...">
            </div>
            <select name="module" class="form-control">
                <option value="">All Modules</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                @endforeach
            </select>
            <select name="action" class="form-control">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                @endforeach
            </select>
            <select name="user" class="form-control">
                <option value="">All Users</option>
                @foreach($users as $userName)
                    <option value="{{ $userName }}" @selected(request('user') === $userName)>{{ $userName }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="From">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}" title="To">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.activity-logs.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="white-space: nowrap;">
                                {{ $log->created_at->format('d M Y') }}
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                {{ $log->user_name ?? 'System' }}
                                @if($log->user_email)<div style="font-size: 11px; color: var(--text-muted);">{{ $log->user_email }}</div>@endif
                            </td>
                            <td>{{ $log->module }}</td>
                            <td><span class="badge {{ $actionBadges[$log->action] ?? 'badge-secondary' }}">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span></td>
                            <td style="max-width: 420px;">
                                {{ $log->description }}
                                @if($log->action === 'updated' && is_array($log->properties) && $log->properties !== [])
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                        @foreach(array_slice($log->properties, 0, 4, true) as $field => $change)
                                            @if(is_array($change) && array_key_exists('new', $change))
                                                <div>{{ str_replace('_', ' ', $field) }}: {{ Str::limit(is_scalar($change['old'] ?? null) ? (string) $change['old'] : '—', 40) }} → {{ Str::limit(is_scalar($change['new']) ? (string) $change['new'] : '—', 40) }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-clipboard-list"></i></div>
                                    <div class="empty-state-title">No activity recorded</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $logs, 'unit' => 'events'])
    </div>
</div>
@endsection
