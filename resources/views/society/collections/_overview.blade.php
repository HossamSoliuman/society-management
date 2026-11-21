<x-chart type="doughnut" :size="170" format="inr" :segments="$overview['segments']"
    :center-value="$overview['center_value']" :center-label="$overview['center_label']" />
<div class="chart-legend" style="margin-top: 20px;">
    @foreach($overview['segments'] as $seg)
        <div class="legend-item">
            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
            <span class="legend-label">{{ $seg['label'] }}</span>
            <span class="legend-value">{!! $seg['amount'] !!} ({{ $seg['pct'] }})</span>
        </div>
    @endforeach
</div>
<div class="summary-item" style="margin-top: 12px; border-top: 1px solid var(--border-color); padding-top: 14px;">
    <span class="summary-label" style="font-weight: 600; color: var(--text-primary);">Total</span>
    <span class="summary-value">{!! $overview['total'] !!}</span>
</div>
