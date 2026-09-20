@extends('society.layouts.app')

@section('title', 'Notifications')

@php
    use App\Support\NotificationKind;
    $indexUrl = fn (array $params = []) => route('society.notifications.index', array_merge(request()->except(['page']), $params));
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Notifications</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Notifications</span>
            </div>
        </div>
        <div class="page-header-actions">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-secondary" {{ $stats['unread'] === 0 ? 'disabled' : '' }}>
                    <i class="fas fa-check-double"></i> Mark all read
                </button>
            </form>
            <form method="POST" action="{{ route('notifications.clear-read') }}" onsubmit="return confirm('Delete every notification you have already read?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-secondary" {{ $stats['read'] === 0 ? 'disabled' : '' }}>
                    <i class="fas fa-broom"></i> Clear read
                </button>
            </form>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-bell', 'iconVariant' => 'primary', 'label' => 'Total', 'value' => $stats['total'], 'trend' => 'All time'])
    @include('society.partials.stat-card', ['icon' => 'fa-envelope', 'iconVariant' => 'warning', 'label' => 'Unread', 'value' => $stats['unread'], 'trend' => $stats['unread'] > 0 ? 'Needs attention' : 'All caught up', 'trendType' => $stats['unread'] > 0 ? 'warning' : 'up'])
    @include('society.partials.stat-card', ['icon' => 'fa-envelope-open', 'iconVariant' => 'success', 'label' => 'Read', 'value' => $stats['read'], 'trend' => $stats['total'] > 0 ? round($stats['read'] / $stats['total'] * 100).'% of total' : '—'])
    @include('society.partials.stat-card', ['icon' => 'fa-calendar-day', 'iconVariant' => 'info', 'label' => 'Today', 'value' => $stats['today'], 'trend' => now()->format('d M Y')])
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div class="tabs">
                    @foreach($tabs as $key => $label)
                        @php($count = match ($key) { 'unread' => $stats['unread'], 'read' => $stats['read'], default => $stats['total'] })
                        <a href="{{ $indexUrl(['tab' => $key]) }}" class="tab {{ $tab === $key ? 'active' : '' }}">
                            {{ $label }} <span class="tab-count">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('society.notifications.index') }}" class="notif-filters">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search notifications...">
                    </div>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($typeLabels as $value => $label)
                            <option value="{{ $value }}" {{ $type === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="notif-date-range">
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control" title="From date">
                        <span class="notif-meta-sep">–</span>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To date">
                    </div>
                    <div class="notif-filter-buttons">
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.notifications.index', ['tab' => $tab]) }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>

                @if($notifications->isNotEmpty())
                    <div class="notif-list">
                        @foreach($notifications as $notification)
                            @php($kind = $notification->data['type'] ?? null)
                            @php($isUnread = $notification->read_at === null)
                            <div class="notif-row {{ $isUnread ? 'is-unread' : '' }}">
                                <div class="notif-icon is-{{ NotificationKind::variant($kind) }}">
                                    <i class="fas {{ NotificationKind::icon($kind) }}"></i>
                                </div>
                                <div class="notif-body">
                                    <div class="notif-title-row">
                                        <a href="{{ route('notifications.open', $notification->id) }}" class="notif-title">{{ $notification->data['title'] ?? 'Notification' }}</a>
                                        <span class="badge badge-{{ NotificationKind::variant($kind) }}">{{ NotificationKind::label($kind) }}</span>
                                        @if($isUnread)
                                            <span class="notif-unread-dot" title="Unread"></span>
                                        @endif
                                    </div>
                                    <div class="notif-meta">
                                        <span title="{{ $notification->created_at->format('d M Y, h:i A') }}">
                                            <i class="far fa-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        <span class="notif-meta-sep">·</span>
                                        <span>{{ $notification->created_at->format('d M Y, h:i A') }}</span>
                                        @if(! $isUnread)
                                            <span class="notif-meta-sep">·</span>
                                            <span><i class="fas fa-check"></i> Read {{ $notification->read_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="notif-actions">
                                    <a href="{{ route('notifications.open', $notification->id) }}" class="action-btn view" title="Open"><i class="fas fa-arrow-up-right-from-square"></i></a>
                                    @if($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="action-btn" title="Mark as read"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @include('society.partials.pagination', ['items' => $notifications, 'unit' => 'notifications'])
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="far fa-bell-slash"></i></div>
                        <div class="empty-state-title">
                            @if(request()->hasAny(['q', 'type', 'from', 'to']))
                                No notifications match your filters
                            @elseif($tab === 'unread')
                                You're all caught up
                            @else
                                No notifications yet
                            @endif
                        </div>
                        <div class="empty-state-text">
                            @if(request()->hasAny(['q', 'type', 'from', 'to']))
                                Try a different search term, type, or date range.
                            @else
                                Alerts about tickets, notices, subscription renewals and AMC expiries will appear here.
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">By Type</h3>
            </div>
            <div class="card-body">
                @forelse($byType as $kind => $count)
                    @php($kindKey = $kind === 'other' ? null : $kind)
                    <a href="{{ $kindKey ? $indexUrl(['type' => $kindKey]) : route('society.notifications.index') }}" class="notif-type-row {{ $type === $kindKey && $kindKey ? 'active' : '' }}">
                        <span class="notif-icon is-{{ NotificationKind::variant($kindKey) }}"><i class="fas {{ NotificationKind::icon($kindKey) }}"></i></span>
                        <span class="notif-type-label">{{ $kind === 'other' ? 'Other' : NotificationKind::label($kindKey) }}</span>
                        <span class="notif-type-count">{{ $count }}</span>
                    </a>
                @empty
                    <div class="notification-empty">Nothing to summarise yet.</div>
                @endforelse
            </div>
        </div>

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Opening a notification marks it as read and takes you to the related ticket, notice, or record.</span>
        </div>
    </div>
</div>
@endsection
