@extends('society.layouts.app')

@section('title', 'Priority Support')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Priority Support</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.support.index') }}">Priority Support</a>
                <span class="breadcrumb-separator">/</span>
                <span>All Requests</span>
            </div>
        </div>
        <a href="{{ route('society.support.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Raise New Request</a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-headset"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Requests</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><i class="fas fa-arrow-up"></i><span>{{ $stats['total_trend'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Open</div>
                    <div class="stat-value">{{ $stats['open'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $stats['open_pct'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-info">
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value">{{ $stats['in_progress'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $stats['in_progress_pct'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Resolved</div>
                    <div class="stat-value">{{ $stats['resolved'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $stats['resolved_pct'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Closed</div>
                    <div class="stat-value">{{ $stats['closed'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $stats['closed_pct'] }}</span></div>
                </div>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.support.index') }}">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by request ID, subject, member name, mobile...">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="priority" class="form-control">
                                <option value="">All Priorities</option>
                                @foreach($priorities as $val => $label)
                                    <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                @foreach($statuses as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="raised_by" class="form-control">
                                <option value="">All</option>
                                <option value="member" {{ request('raised_by') === 'member' ? 'selected' : '' }}>Member</option>
                                <option value="staff_admin" {{ request('raised_by') === 'staff_admin' ? 'selected' : '' }}>Staff / Admin</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.support.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabs + table --}}
        <div class="card">
            <div class="card-body">
                <div class="tabs">
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('society.support.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}" class="tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request ID</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Raised By</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Raised On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr>
                                    <td>{{ $requests->firstItem() + $loop->index }}</td>
                                    <td style="font-weight: 600; white-space: nowrap;">{{ $request->request_id }}</td>
                                    <td>{{ $request->subject }}</td>
                                    <td><span class="badge {{ $request->categoryBadgeClass() }}">{{ $request->category }}</span></td>
                                    <td>
                                        <div style="font-weight: 500;">{{ $request->raised_by_name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $request->flat_no }}</div>
                                    </td>
                                    <td><span class="badge {{ $request->priorityBadgeClass() }}">{{ $request->priorityLabel() }}</span></td>
                                    <td><span class="status-badge {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span></td>
                                    <td style="white-space: nowrap;">
                                        <div>{{ $request->raised_at?->format('d M Y') }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $request->raised_at?->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.support.show', $request) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-headset"></i></div>
                                            <div class="empty-state-title">No requests found</div>
                                            <div class="empty-state-text">Try adjusting your filters or raise a new request.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $requests, 'firstLast' => false, 'side' => 2, 'unit' => 'requests'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Requests by Category --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Requests by Category <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">(This Month)</span></div>
                @include('society.partials.donut', [
                    'segments' => collect($categoryDonut['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                    'centerValue' => $categoryDonut['center_value'],
                    'centerLabel' => $categoryDonut['center_label'],
                    'size' => 170,
                    'stroke' => 14,
                ])
                <div class="chart-legend" style="margin-top: 20px;">
                    @foreach($categoryDonut['segments'] as $seg)
                        <div class="legend-item">
                            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                            <span class="legend-label">{{ $seg['label'] }}</span>
                            <span class="legend-value">{{ $seg['value'] }} ({{ $seg['pct'] }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Priority Distribution --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Priority Distribution</div>
                @foreach($priorityBars as $bar)
                    <div style="margin-bottom: {{ $loop->last ? '0' : '16px' }};">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                            <span style="color: var(--text-secondary);">{{ $bar['label'] }}</span>
                            <span style="font-weight: 600;">{{ $bar['value'] }} ({{ $bar['pct'] }})</span>
                        </div>
                        <div class="progress-bar"><div class="progress-bar-fill" style="width: {{ $bar['width'] }}%; background: {{ $bar['color'] }};"></div></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Raise New Request', 'desc' => 'Create a new support request', 'url' => route('society.support.create'), 'color' => 'orange'],
            ['icon' => 'fa-list', 'label' => 'My Requests', 'desc' => 'View your requests', 'url' => route('society.support.index'), 'color' => 'green'],
            ['icon' => 'fa-book', 'label' => 'Knowledge Base', 'desc' => 'Find answers quickly', 'url' => route('society.support.index'), 'color' => 'blue'],
            ['icon' => 'fa-headset', 'label' => 'Contact Support', 'desc' => 'Get in touch with us', 'url' => route('society.support.index'), 'color' => 'purple'],
        ]])

        {{-- Note --}}
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Our support team will respond to your request as soon as possible.</span>
        </div>
    </div>
</div>
@endsection
