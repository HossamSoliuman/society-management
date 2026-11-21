@php($o = $d['occupancy'])
<div class="card-header"><div class="card-title">Occupancy Overview</div></div>
<div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
    <x-chart type="doughnut" :size="120" label="Occupancy overview"
        :labels="['Occupied', 'Vacant', 'Under Maintenance']"
        :datasets="[['data' => [$o['occupied'], $o['vacant'], $o['maintenance']], 'colors' => ['#10B981', '#3B82F6', '#F59E0B']]]"
        :center-value="(string) $o['total_units']" center-label="Total Units" />
    <div class="chart-legend" style="flex: 1;">
        <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Occupied</span><span class="legend-value">{{ $o['occupied'] }} ({{ $o['occupied_pct'] }}%)</span></div>
        <div class="legend-item"><span class="legend-dot" style="background: #3B82F6;"></span><span class="legend-label">Vacant</span><span class="legend-value">{{ $o['vacant'] }} ({{ $o['vacant_pct'] }}%)</span></div>
        <div class="legend-item"><span class="legend-dot" style="background: #F59E0B;"></span><span class="legend-label">Under Maintenance</span><span class="legend-value">{{ $o['maintenance'] }} ({{ $o['maintenance_pct'] }}%)</span></div>
    </div>
</div>
<div class="card-footer" style="padding: 12px 20px;">
    <a href="{{ route('society.units.index') }}" class="btn-link">View Units <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
</div>
