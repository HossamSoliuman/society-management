@extends('society.layouts.app')

@section('title', 'Complaint Management')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Complaint Management</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Complaints</span>
            </div>
        </div>
        <a href="{{ route('society.complaints.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Log Complaint</a>
    </div>
</div>

<div class="stats-grid">
    @include('society.partials.stat-card', ['icon' => 'fa-circle-exclamation', 'iconVariant' => 'danger', 'label' => 'Open', 'value' => number_format($stats['open'])])
    @include('society.partials.stat-card', ['icon' => 'fa-spinner', 'iconVariant' => 'warning', 'label' => 'In Progress', 'value' => number_format($stats['in_progress'])])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Resolved', 'value' => number_format($stats['resolved'])])
    @include('society.partials.stat-card', ['icon' => 'fa-user-slash', 'iconVariant' => 'purple', 'label' => 'Unassigned', 'value' => number_format($stats['unassigned'])])
    @include('society.partials.stat-card', ['icon' => 'fa-stopwatch', 'iconVariant' => 'info', 'label' => 'Avg. Resolution', 'value' => e($stats['avg_resolution'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.complaints.index') }}" style="display: grid; grid-template-columns: 1fr 170px 150px 170px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search number, subject, resident, flat...">
            </div>
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <select name="priority" class="form-control">
                <option value="">All Priorities</option>
                @foreach($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="assigned" class="form-control">
                <option value="">Anyone</option>
                <option value="me" @selected(request('assigned') === 'me')>Assigned to me</option>
                <option value="none" @selected(request('assigned') === 'none')>Unassigned</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.complaints.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="tabs">
            @foreach($tabs as $key => $label)
                <a href="{{ route('society.complaints.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}" class="tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Complaint</th>
                        <th>Resident</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Raised</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                        <tr>
                            <td>
                                <a href="{{ route('society.complaints.show', $complaint) }}" style="font-weight: 600;">{{ $complaint->subject }}</a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $complaint->ticket_number }}</div>
                            </td>
                            <td>
                                <span style="display: block;">{{ $complaint->raised_by_name ?? $complaint->member?->name ?? '—' }}</span>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $complaint->flat_no ?? '—' }}</span>
                            </td>
                            <td><span class="badge {{ $complaint->categoryBadgeClass() }}">{{ $complaint->category }}</span></td>
                            <td><span class="badge {{ $complaint->priorityBadgeClass() }}">{{ $complaint->priorityLabel() }}</span></td>
                            <td>{{ $complaint->assignedTo?->name ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $complaint->raised_at?->diffForHumans() ?? '—' }}</td>
                            <td><span class="status-badge {{ $complaint->statusBadgeClass() }}">{{ $complaint->statusLabel() }}</span></td>
                            <td><a href="{{ route('society.complaints.show', $complaint) }}" class="action-btn view" title="Open"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-triangle-exclamation"></i></div>
                                    <div class="empty-state-title">No complaints found</div>
                                    <div class="empty-state-text">Complaints raised by residents from the portal or logged by the office appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $complaints, 'unit' => 'complaints'])
    </div>
</div>
@endsection
