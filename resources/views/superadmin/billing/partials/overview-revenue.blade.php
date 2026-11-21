@php
    $chart = $d['chart'];
    $hasData = array_sum($chart['collected']) > 0 || array_sum($chart['invoiced']) > 0;
@endphp
<div class="card-header">
    <div class="card-title">Revenue Overview</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $d['period']->label() }}</span>
</div>
<div class="card-body">
    @if(! $hasData)
        <div class="empty-state" style="height: 260px; display: flex; flex-direction: column; justify-content: center;">
            <div class="empty-state-icon"><i class="fas fa-chart-column"></i></div>
            <div class="empty-state-title">No invoices or payments in this period</div>
        </div>
    @else
        <x-chart type="bar" :height="260" format="inr" legend label="Invoiced and collected revenue"
            :labels="$chart['labels']"
            :datasets="[
                ['label' => 'Collected', 'data' => $chart['collected'], 'color' => 'var(--primary)'],
                ['label' => 'Invoiced', 'data' => $chart['invoiced'], 'color' => '#CBD5E1'],
            ]" />
    @endif
</div>
