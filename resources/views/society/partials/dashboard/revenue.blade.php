@php($r = $d['revenue'])
<div class="card-header">
    <div class="card-title">Monthly Revenue Trend</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $r['ref_year'] }}</span>
</div>
<div class="card-body" style="flex: 1;">
    <x-chart type="area" :height="200" format="inr" label="Monthly revenue trend"
        :labels="$r['months']"
        :datasets="[['label' => 'Collected', 'data' => $r['points'], 'color' => 'var(--primary)']]" />
    <div style="display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
        <div>
            <div style="font-size: 11px; color: var(--text-muted);">Total Revenue ({{ $r['ref_year'] }})</div>
            <div style="font-size: 15px; font-weight: 700; color: var(--success);">&#8377; {{ $r['ytd_fmt'] }}</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 11px; color: var(--text-muted);">vs Last Year</div>
            @if(is_null($r['vs_last_year']))
                <div style="font-size: 15px; font-weight: 700; color: var(--text-muted);">—</div>
            @else
                <div style="font-size: 15px; font-weight: 700; color: {{ $r['vs_last_year'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    <i class="fas fa-arrow-{{ $r['vs_last_year'] >= 0 ? 'up' : 'down' }}" style="font-size: 11px;"></i> {{ abs($r['vs_last_year']) }}%
                </div>
            @endif
        </div>
    </div>
</div>
