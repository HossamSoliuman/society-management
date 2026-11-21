@php
    $categories = $d['revenueByCategory'];
    $catColors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6'];
    $segments = collect($categories)->values()->map(fn ($cat, $i) => [
        'label' => $cat['name'],
        'value' => $cat['amount'],
        'color' => $catColors[$i % count($catColors)],
    ])->all();
    $total = (float) collect($categories)->sum('amount');
    $totalShort = match (true) {
        $total >= 100000 => round($total / 100000, 1).'L',
        $total >= 1000 => round($total / 1000, 1).'K',
        default => (string) (int) $total,
    };
@endphp
<div class="card-header">
    <div class="card-title">Revenue By Category</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $d['period']->label() }}</span>
</div>
<div class="card-body" style="display: flex; align-items: center; gap: 16px;">
    @if(empty($categories))
        <div style="font-size: 13px; color: var(--text-muted);">No payments in this period.</div>
    @else
        <x-chart type="doughnut" :size="130" format="inr" :segments="$segments" label="Revenue by category"
            :center-value="'₹'.$totalShort" center-label="Total" />
        <div class="chart-legend" style="flex: 1;">
            @foreach($segments as $i => $segment)
            <div class="legend-item">
                <span class="legend-dot" style="background: {{ $segment['color'] }};"></span>
                <span class="legend-label">{{ $segment['label'] }}</span>
                <span class="legend-value">&#8377; {{ number_format($segment['value']) }} ({{ $categories[$i]['percentage'] }}%)</span>
            </div>
            @endforeach
        </div>
    @endif
</div>
<div style="padding: 0 20px 16px;">
    <a href="{{ route('superadmin.reports.revenue') }}" class="btn-link" style="font-size: 12px;">View full report <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
</div>
