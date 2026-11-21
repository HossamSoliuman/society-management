@php
    $s = $d['stats'];
    $periodLabel = $d['period']->label();
@endphp
@foreach([
    ['label' => 'Invoiced ('.$periodLabel.')', 'icon' => 'fa-indian-rupee-sign', 'color' => 'blue', 'value' => $s['invoiced'], 'change' => $s['invoiced_change']],
    ['label' => 'Collected ('.$periodLabel.')', 'icon' => 'fa-wallet', 'color' => 'green', 'value' => $s['collected'], 'change' => $s['collected_change']],
] as $card)
    <div class="stat-card">
        <div class="stat-icon {{ $card['color'] }}"><i class="fas {{ $card['icon'] }}"></i></div>
        <div class="stat-info">
            <div class="stat-label">{{ $card['label'] }}</div>
            <div class="stat-value">&#8377; {{ number_format($card['value']) }}</div>
            @if($card['change'] === null)
                <div class="stat-trend" style="color: var(--text-muted);">Nothing in the previous period</div>
            @else
                <div class="stat-trend {{ $card['change'] >= 0 ? 'up' : 'down' }}"><i class="fas {{ $card['change'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($card['change']) }}% {{ $d['period']->comparisonLabel() }}</div>
            @endif
        </div>
    </div>
@endforeach
<div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-file-invoice"></i></div>
    <div class="stat-info">
        <div class="stat-label">Total Outstanding</div>
        <div class="stat-value">&#8377; {{ number_format($s['outstanding']) }}</div>
        <div class="stat-trend" style="color: var(--text-muted);">{{ number_format($s['outstanding_count']) }} unpaid {{ Str::plural('invoice', $s['outstanding_count']) }}</div>
    </div>
</div>
<div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="stat-info">
        <div class="stat-label">Overdue Amount</div>
        <div class="stat-value">&#8377; {{ number_format($s['overdue']) }}</div>
        <div class="stat-trend {{ $s['overdue_count'] > 0 ? 'down' : '' }}" @if($s['overdue_count'] === 0) style="color: var(--text-muted);" @endif>{{ number_format($s['overdue_count']) }} overdue {{ Str::plural('invoice', $s['overdue_count']) }}</div>
    </div>
</div>
