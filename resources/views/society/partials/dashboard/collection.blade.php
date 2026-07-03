@php($c = $d['collection'])
<div class="card-header"><div class="card-title">Collection Overview</div></div>
<div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
    @include('society.partials.donut', [
        'segments' => [
            ['value' => $c['collected'], 'color' => '#10B981'],
            ['value' => $c['pending'], 'color' => '#F59E0B'],
            ['value' => $c['overdue'], 'color' => '#EF4444'],
        ],
        'centerValue' => $c['collected_pct'].'%',
        'centerLabel' => 'Collected',
        'size' => 150,
        'stroke' => 13,
    ])
    <div class="chart-legend" style="flex: 1;">
        <div class="legend-item">
            <span class="legend-dot" style="background: #10B981;"></span>
            <span class="legend-label">Collected</span>
            <span class="legend-value">&#8377; {{ $c['collected_fmt'] }} ({{ $c['collected_pct'] }}%)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background: #F59E0B;"></span>
            <span class="legend-label">Pending</span>
            <span class="legend-value">&#8377; {{ $c['pending_fmt'] }} ({{ $c['pending_pct'] }}%)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background: #EF4444;"></span>
            <span class="legend-label">Overdue</span>
            <span class="legend-value">&#8377; {{ $c['overdue_fmt'] }} ({{ $c['overdue_pct'] }}%)</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background: #cbd5e1;"></span>
            <span class="legend-label">Total Demand</span>
            <span class="legend-value">&#8377; {{ $c['total_demand_fmt'] }}</span>
        </div>
    </div>
</div>
<div class="card-footer" style="padding: 12px 20px;">
    <a href="{{ route('society.collections.index') }}" class="btn btn-outline-primary" style="width: 100%;">View Collection Report <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
</div>
