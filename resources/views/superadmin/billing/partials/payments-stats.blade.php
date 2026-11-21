<div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
    <div class="stat-info">
        <div class="stat-label">Collections ({{ $d['period']->label() }})</div>
        <div class="stat-value">&#8377; {{ number_format($d['successful']) }}</div>
        @if($d['successful_change'] === null)
            <div class="stat-trend" style="color: var(--text-muted);">Nothing in the previous period</div>
        @else
            <div class="stat-trend {{ $d['successful_change'] >= 0 ? 'up' : 'down' }}"><i class="fas {{ $d['successful_change'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($d['successful_change']) }}% {{ $d['period']->comparisonLabel() }}</div>
        @endif
    </div>
</div>
<div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
    <div class="stat-info">
        <div class="stat-label">Payments Received</div>
        <div class="stat-value">{{ number_format($d['successful_count']) }}</div>
        <div class="stat-trend" style="color: var(--text-muted);">{{ strtolower($d['period']->label()) }}</div>
    </div>
</div>
<div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
    <div class="stat-info">
        <div class="stat-label">Pending Payments</div>
        <div class="stat-value">{{ number_format($d['pending_count']) }}</div>
        <div style="font-size: 12px; color: var(--warning); font-weight: 600;">&#8377; {{ number_format($d['pending']) }}</div>
    </div>
</div>
<div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
    <div class="stat-info">
        <div class="stat-label">Failed Payments</div>
        <div class="stat-value">{{ number_format($d['failed_count']) }}</div>
        <div style="font-size: 12px; color: var(--danger); font-weight: 600;">&#8377; {{ number_format($d['failed']) }}</div>
    </div>
</div>
