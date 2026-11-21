@php
    $plans = $d['revenueByPlan'];
    $planColors = ['#E84B1E', '#10B981', '#F59E0B', '#3B82F6', '#8B5CF6', '#14B8A6'];
    $segments = collect($plans)->values()->map(fn ($plan, $i) => [
        'label' => $plan['name'],
        'value' => $plan['amount'],
        'color' => $planColors[$i % count($planColors)],
    ])->all();
    $planTotal = (float) collect($plans)->sum('amount');
    $planTotalShort = match (true) {
        $planTotal >= 100000 => round($planTotal / 100000, 1).'L',
        $planTotal >= 1000 => round($planTotal / 1000, 1).'K',
        default => (string) (int) $planTotal,
    };
@endphp
<div class="card-header">
    <div class="card-title">Revenue by Plan</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $d['period']->label() }}</span>
</div>
<div class="card-body">
    @if(empty($plans))
        <div style="font-size: 13px; color: var(--text-muted);">No payments in this period.</div>
    @else
        <div style="display: flex; align-items: center; gap: 20px;">
            <x-chart type="doughnut" :size="140" format="inr" :segments="$segments" label="Revenue by plan"
                :center-value="'₹'.$planTotalShort" center-label="Total" />
            <div class="chart-legend" style="flex: 1;">
                @foreach($segments as $i => $segment)
                <div class="legend-item">
                    <span class="legend-dot" style="background: {{ $segment['color'] }};"></span>
                    <span class="legend-label">{{ $segment['label'] }}</span>
                    <span class="legend-value">&#8377; {{ number_format($segment['value']) }} ({{ $plans[$i]['percentage'] }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
