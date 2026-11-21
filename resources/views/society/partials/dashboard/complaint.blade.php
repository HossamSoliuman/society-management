@php
    $cp = $d['complaint'];
    $tot = $cp['total'] ?: 1;
    $pct = fn ($v) => (int) round($v / $tot * 100);
@endphp
<div class="card-header"><div class="card-title">Complaint Summary</div></div>
<div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
    <x-chart type="doughnut" :size="120" label="Complaint summary"
        :labels="['Open', 'In Progress', 'Resolved']"
        :datasets="[['data' => [$cp['open'], $cp['in_progress'], $cp['resolved']], 'colors' => ['#EF4444', '#F59E0B', '#10B981']]]"
        :center-value="(string) $cp['total']" center-label="Total" />
    <div class="chart-legend" style="flex: 1;">
        <div class="legend-item"><span class="legend-dot" style="background: #EF4444;"></span><span class="legend-label">Open</span><span class="legend-value">{{ $cp['open'] }} ({{ $pct($cp['open']) }}%)</span></div>
        <div class="legend-item"><span class="legend-dot" style="background: #F59E0B;"></span><span class="legend-label">In Progress</span><span class="legend-value">{{ $cp['in_progress'] }} ({{ $pct($cp['in_progress']) }}%)</span></div>
        <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Resolved</span><span class="legend-value">{{ $cp['resolved'] }} ({{ $pct($cp['resolved']) }}%)</span></div>
    </div>
</div>
<div class="card-footer" style="padding: 12px 20px;">
    <a href="{{ route('society.complaints.index') }}" class="btn-link">View Complaints <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
</div>
