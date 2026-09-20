@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $recent = $user ? $user->unreadNotifications()->latest()->limit(5)->get() : collect();
@endphp
<div class="notification-dropdown">
    <button class="icon-btn" type="button" aria-label="Notifications">
        <i class="far fa-bell"></i>
        @if($unreadCount > 0)
            <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu notification-menu">
        <div class="dropdown-menu-header notification-menu-header">
            <span>Notifications</span>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="notification-read-all">Mark all read</button>
                </form>
            @endif
        </div>

        @forelse($recent as $notification)
            <a href="{{ route('notifications.open', $notification->id) }}" class="dropdown-menu-item notification-item">
                <i class="fas {{ match($notification->data['type'] ?? '') {
                    'ticket_created', 'ticket_replied', 'ticket_status' => 'fa-headset',
                    'subscription_renewal' => 'fa-rotate',
                    'amc_expiry' => 'fa-file-contract',
                    'notice' => 'fa-clipboard-list',
                    default => 'fa-bullhorn',
                } }}"></i>
                <span class="notification-item-body">
                    <span class="notification-item-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                    <span class="notification-item-time">{{ $notification->created_at->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <div class="notification-empty">You're all caught up.</div>
        @endforelse

        @if(isset($links))
            <hr class="dropdown-menu-divider">
            {{ $links }}
        @endif
    </div>
</div>
