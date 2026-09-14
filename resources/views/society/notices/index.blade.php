@extends('society.layouts.app')

@section('title', 'Notices')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Notices &amp; Announcements</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Notices</span>
            </div>
        </div>
    </div>
</div>

@if($pendingAck > 0)
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ $pendingAck }} notice{{ $pendingAck === 1 ? '' : 's' }} require your acknowledgement.
    </div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.notices.index') }}">
                    <div style="display: grid; grid-template-columns: 1fr 180px 160px auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search notices...">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                @foreach(['general' => 'General', 'maintenance' => 'Maintenance', 'billing' => 'Billing', 'event' => 'Event'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="priority" class="form-control">
                                <option value="">All Priorities</option>
                                @foreach(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('priority') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.notices.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @forelse($notices as $notice)
            @php($acked = $notice->acknowledgements->isNotEmpty())
            <div class="card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start;">
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                @if($notice->pin_to_dashboard)<i class="fas fa-thumbtack" style="color: var(--orange);" title="Pinned"></i>@endif
                                <a href="{{ route('society.notices.show', $notice) }}" style="font-weight: 600; font-size: 15px;">{{ $notice->title }}</a>
                                <span class="badge {{ $notice->priorityBadgeClass() }}">{{ ucfirst($notice->priority) }}</span>
                                <span class="badge badge-secondary">{{ ucfirst($notice->notice_type) }}</span>
                                @if($notice->society_id)<span class="badge badge-info">For {{ $notice->society?->name }}</span>@endif
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px;">{{ $notice->short_description ?: Str::limit(strip_tags($notice->content), 160) }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                                Published {{ $notice->publish_at?->format('d M Y') }}
                                @if($notice->expires_at) · Expires {{ $notice->expires_at->format('d M Y') }} @endif
                            </div>
                        </div>
                        <div style="flex-shrink: 0;">
                            @if($notice->require_acknowledgement)
                                @if($acked)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Acknowledged</span>
                                @else
                                    <form method="POST" action="{{ route('society.notices.acknowledge', $notice) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Acknowledge</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-bullhorn"></i></div>
                        <div class="empty-state-title">No notices</div>
                        <div class="empty-state-text">Notices from the platform that target your society will appear here.</div>
                    </div>
                </div>
            </div>
        @endforelse

        @include('society.partials.pagination', ['items' => $notices, 'unit' => 'notices'])
    </div>

    <div>
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Notices marked "Acknowledge" must be confirmed by every society user; the platform tracks who has read them.</span>
        </div>
    </div>
</div>
@endsection
