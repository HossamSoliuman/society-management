@php
    $s = $d['stats'];
    $periodLabel = strtolower($d['period']->label());
@endphp
<div class="stat-card">
    <div class="stat-icon blue">
        <i class="fas fa-building"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">Total Societies</div>
        <div class="stat-value">{{ number_format($s['total_societies']) }}</div>
        <div class="stat-trend {{ $s['new_societies'] > 0 ? 'up' : '' }}" @if($s['new_societies'] === 0) style="color: var(--text-muted);" @endif>
            @if($s['new_societies'] > 0)<i class="fas fa-arrow-up"></i>@endif
            <span>{{ $s['new_societies'] }} new {{ $periodLabel }}</span>
        </div>
    </div>
</div>

<div class="stat-card">
    <div class="stat-icon green">
        <i class="fas fa-check-circle"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">Active Societies</div>
        <div class="stat-value">{{ number_format($s['active_societies']) }}</div>
        <div class="stat-trend" style="color: {{ $s['expiring_soon'] > 0 ? 'var(--warning)' : 'var(--text-muted)' }};">
            <span>{{ $s['expiring_soon'] }} expiring soon</span>
        </div>
    </div>
</div>

<div class="stat-card">
    <div class="stat-icon red">
        <i class="fas fa-hourglass-end"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">Expired Societies</div>
        <div class="stat-value">{{ number_format($s['expired_societies']) }}</div>
        <div class="stat-trend {{ $s['lapsed'] > 0 ? 'down' : '' }}" @if($s['lapsed'] === 0) style="color: var(--text-muted);" @endif>
            <span>{{ $s['lapsed'] }} lapsed {{ $periodLabel }}</span>
        </div>
    </div>
</div>

<div class="stat-card">
    <div class="stat-icon purple">
        <i class="fas fa-users"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">Total Units</div>
        <div class="stat-value">{{ number_format($s['total_units']) }}</div>
        <div class="stat-trend {{ $s['new_units'] > 0 ? 'up' : '' }}" @if($s['new_units'] === 0) style="color: var(--text-muted);" @endif>
            @if($s['new_units'] > 0)<i class="fas fa-arrow-up"></i>@endif
            <span>{{ number_format($s['new_units']) }} registered {{ $periodLabel }}</span>
        </div>
    </div>
</div>

<div class="stat-card">
    <div class="stat-icon orange">
        <i class="fas fa-indian-rupee-sign"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">Revenue ({{ $d['period']->label() }})</div>
        <div class="stat-value">&#8377; {{ number_format($s['revenue'] / 100000, 2) }}L</div>
        @if($s['revenue_change'] === null)
            <div class="stat-trend" style="color: var(--text-muted);"><span>No payments in the previous period</span></div>
        @else
            <div class="stat-trend {{ $s['revenue_change'] >= 0 ? 'up' : 'down' }}">
                <i class="fas {{ $s['revenue_change'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                <span>{{ abs($s['revenue_change']) }}% {{ $d['period']->comparisonLabel() }}</span>
            </div>
        @endif
    </div>
</div>

<div class="stat-card">
    <div class="stat-icon teal">
        <i class="fas fa-wallet"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">&#8377; {{ number_format($s['total_revenue'] / 100000, 2) }}L</div>
        <div class="stat-trend" style="color: var(--text-muted);">
            <span>All time revenue</span>
        </div>
    </div>
</div>
