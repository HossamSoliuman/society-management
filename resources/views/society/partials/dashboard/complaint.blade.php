@php
    $cp = $d['complaint'];
    $tot = $cp['total'] ?: 1;
    $pct = fn ($v) => (int) round($v / $tot * 100);
@endphp
<div class="card-header"><div class="card-title">Complaint Summary</div></div>
<div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
    @include('society.partials.donut', [
        'segments' => [
            ['value' => $cp['open'], 'color' => '#EF4444'],
            ['value' => $cp['in_progress'], 'color' => '#F59E0B'],
            ['value' => $cp['resolved'], 'color' => '#10B981'],
        ],
        'centerValue' => (string) $cp['total'],
        'centerLabel' => 'Total',
        'size' => 120,
        'stroke' => 14,
    ])
    <div class="chart-legend" style="flex: 1;">
        <div class="legend-item"><span class="legend-dot" style="background: #EF4444;"></span><span class="legend-label">Open</span><span class="legend-value">{{ $cp['open'] }} ({{ $pct($cp['open']) }}%)</span></div>
        <div class="legend-item"><span class="legend-dot" style="background: #F59E0B;"></span><span class="legend-label">In Progress</span><span class="legend-value">{{ $cp['in_progress'] }} ({{ $pct($cp['in_progress']) }}%)</span></div>
        <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Resolved</span><span class="legend-value">{{ $cp['resolved'] }} ({{ $pct($cp['resolved']) }}%)</span></div>
    </div>
</div>
<div class="card-footer" style="padding: 12px 20px;">
    <a href="{{ route('society.placeholder', ['page' => 'Complaint Management']) }}" class="btn-link">View Complaints <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
</div>
