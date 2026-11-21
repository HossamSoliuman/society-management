<x-chart type="doughnut" :size="170" format="inr" :segments="$donut['segments']"
    :center-value="$donut['center_value']" :center-label="$donut['center_label']" />
<div class="chart-legend" style="margin-top: 20px;">
    @forelse($donut['segments'] as $seg)
        <div class="legend-item">
            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
            <span class="legend-label">{{ $seg['label'] }}</span>
            <span class="legend-value">{!! $seg['amount'] !!} ({{ $seg['pct'] }})</span>
        </div>
    @empty
        <div style="font-size: 13px; color: var(--text-muted);">No expenses in this period.</div>
    @endforelse
</div>
