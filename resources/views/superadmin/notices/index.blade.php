@extends('superadmin.layouts.app')

@section('title', 'Notice Management')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Notice Management</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('superadmin.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Communication</span>
                <span class="breadcrumb-separator">/</span>
                <span>Notice Management</span>
            </div>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-file-lines"></i> Notice Templates</a>
            <a href="{{ route('superadmin.notices.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Notice</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif

<div class="stats-grid stats-grid-5">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-file-lines"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Notices</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>All time notices</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-paper-plane"></i></div>
        <div class="stat-info">
            <div class="stat-label">Published</div>
            <div class="stat-value">{{ $stats['published'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Published notices</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Scheduled</div>
            <div class="stat-value">{{ $stats['scheduled'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Upcoming notices</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-box-archive"></i></div>
        <div class="stat-info">
            <div class="stat-label">Drafts</div>
            <div class="stat-value">{{ $stats['drafts'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Saved as drafts</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expired</div>
            <div class="stat-value">{{ $stats['expired'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Past expiry notices</span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('superadmin.notices.index') }}" class="filter-bar">
            <div class="filter-item" style="flex: 1;">
                <div class="header-search" style="max-width: 100%;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search notice title or content...">
                </div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <label class="form-label">Notice Type</label>
                <select name="notice_type" class="form-control" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach(['general' => 'General', 'maintenance' => 'Maintenance', 'billing' => 'Billing', 'event' => 'Event'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('notice_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach(['published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Draft', 'expired' => 'Expired'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-control" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    @foreach(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <label class="form-label">Published On</label>
                <div style="display: flex; gap: 6px;">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control" style="max-width: 150px;">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" style="max-width: 150px;">
                </div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto; display: flex; gap: 8px; align-self: flex-end;">
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button>
                <a href="{{ route('superadmin.notices.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>

        @php
            $typeMeta = [
                'general' => ['icon' => 'fa-bullhorn', 'badge' => 'badge-info', 'color' => 'blue'],
                'maintenance' => ['icon' => 'fa-fan', 'badge' => 'badge-success', 'color' => 'green'],
                'billing' => ['icon' => 'fa-indian-rupee-sign', 'badge' => 'badge-warning', 'color' => 'orange'],
                'event' => ['icon' => 'fa-calendar-day', 'badge' => 'badge-info', 'color' => 'purple'],
            ];
        @endphp

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Published On</th>
                        <th>Expires On</th>
                        <th>Audience</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notices as $notice)
                        @php $meta = $typeMeta[$notice->notice_type] ?? $typeMeta['general']; @endphp
                        <tr>
                            <td>
                                <div style="display: flex; gap: 12px; align-items: flex-start;">
                                    <div class="stat-icon {{ $meta['color'] }}" style="width: 40px; height: 40px; flex: 0 0 40px;"><i class="fas {{ $meta['icon'] }}"></i></div>
                                    <div>
                                        <div style="font-weight: 600;">{{ $notice->title }}</div>
                                        <div style="font-size: 12px; color: var(--text-muted); max-width: 320px;">{{ Str::limit($notice->short_description, 70) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge {{ $meta['badge'] }}">{{ ucfirst($notice->notice_type) }}</span></td>
                            <td><span class="badge {{ $notice->priorityBadgeClass() }}">{{ ucfirst($notice->priority) }}</span></td>
                            <td>
                                <div style="font-size: 13px;">{{ optional($notice->publish_at)->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ optional($notice->publish_at)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($notice->expires_at)
                                    <div style="font-size: 13px;">{{ $notice->expires_at->format('d M Y') }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $notice->expires_at->format('h:i A') }}</div>
                                @else
                                    <span style="color: var(--text-muted);">No expiry</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 13px;">{{ ucwords(str_replace('_', ' ', $notice->audience_type)) }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $notice->estimated_recipients }} recipients</div>
                            </td>
                            <td><span class="badge {{ $notice->statusBadgeClass() }}">{{ ucfirst($notice->status) }}</span></td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-pen"></i></button>
                                    <button class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-file-lines"></i></div>
                                    <div class="empty-state-title">No notices found</div>
                                    <div class="empty-state-text">Create your first notice to get started.</div>
                                    <a href="{{ route('superadmin.notices.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Notice</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('superadmin.components.pagination', ['items' => $notices])
    </div>
</div>
@endsection
