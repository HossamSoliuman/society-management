@php
    $methods = $d['methods'];
    $methodColors = ['#E84B1E', '#10B981', '#F59E0B', '#8B5CF6', '#64748b', '#3B82F6'];
    $segments = collect($methods)->values()->map(fn ($method, $i) => [
        'label' => $method['name'],
        'value' => $method['amount'],
        'color' => $methodColors[$i % count($methodColors)],
    ])->all();
@endphp
<div class="card-header">
    <div class="card-title">Payment Methods Breakdown</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $d['period']->label() }}</span>
</div>
<div class="card-body" style="display: flex; align-items: center; gap: 16px;">
    @if(empty($methods))
        <div style="font-size: 13px; color: var(--text-muted);">No payments in this period.</div>
    @else
        <x-chart type="doughnut" :size="110" format="inr" :segments="$segments" label="Payment methods" />
        <div class="chart-legend" style="flex: 1;">
            @foreach($segments as $i => $segment)
            <div class="legend-item">
                <span class="legend-dot" style="background: {{ $segment['color'] }};"></span>
                <span class="legend-label">{{ $segment['label'] }}</span>
                <span class="legend-value">&#8377; {{ number_format($segment['value']) }} ({{ $methods[$i]['percentage'] }}%)</span>
            </div>
            @endforeach
        </div>
    @endif
</div>
