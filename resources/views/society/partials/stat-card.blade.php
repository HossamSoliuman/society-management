@php
    /**
     * @var string $icon          Font Awesome icon class (e.g. "fa-users")
     * @var string $iconVariant   peach|primary|success|warning|danger|purple|info|pink
     * @var string $label
     * @var string $value
     * @var string|null $trend
     * @var string|null $trendType  up|down|muted
     */
    $variantMap = [
        'peach' => 'is-primary', 'primary' => 'is-primary',
        'success' => 'is-success', 'warning' => 'is-warning',
        'danger' => 'is-danger', 'purple' => 'is-purple',
        'info' => 'is-info', 'pink' => 'is-pink',
    ];
    $iconClass = $variantMap[$iconVariant ?? 'peach'] ?? 'is-primary';
    $trendType = $trendType ?? 'muted';
    $trendColor = match ($trendType) {
        'up', 'success' => 'var(--success)',
        'down', 'danger' => 'var(--danger)',
        'warning' => 'var(--warning)',
        'purple' => 'var(--purple)',
        default => 'var(--text-muted)',
    };
@endphp
<div class="stat-card">
    <div class="stat-icon {{ $iconClass }}">
        <i class="fas {{ $icon }}"></i>
    </div>
    <div class="stat-info">
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value">{!! $value !!}</div>
        @if(!empty($trend))
            <div class="stat-trend" style="color: {{ $trendColor }};">
                @if($trendType === 'up')<i class="fas fa-arrow-up"></i>@elseif($trendType === 'down')<i class="fas fa-arrow-down"></i>@endif
                <span>{{ $trend }}</span>
            </div>
        @endif
    </div>
</div>
