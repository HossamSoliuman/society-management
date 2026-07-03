@php
    $r = $d['revenue'];
    $max = $r['max'] ?: 1;
    $left = 38; $right = 315; $top = 12; $bottom = 150;
    $span = $right - $left;
    $count = count($r['points']);
    $coords = [];
    foreach ($r['points'] as $i => $val) {
        $x = $left + ($count > 1 ? $i * ($span / ($count - 1)) : 0);
        $y = $bottom - ($val / $max) * ($bottom - $top);
        $coords[] = [round($x, 1), round($y, 1)];
    }
    $polyline = implode(' ', array_map(fn ($c) => $c[0].','.$c[1], $coords));
    $areaPath = 'M'.$coords[0][0].','.$bottom.' L'.implode(' L', array_map(fn ($c) => $c[0].','.$c[1], $coords)).' L'.end($coords)[0].','.$bottom.' Z';
    // Axis labels at 0 / 50% / 100% of max.
    $fmtAxis = function ($v) {
        if ($v >= 100000) { return '₹'.rtrim(rtrim(number_format($v / 100000, 1), '0'), '.').'L'; }
        if ($v >= 1000) { return '₹'.rtrim(rtrim(number_format($v / 1000, 1), '0'), '.').'K'; }
        return '₹'.(int) $v;
    };
    $gridLevels = [$max, $max * 0.5, 0];
@endphp
<div class="card-header">
    <div class="card-title">Monthly Revenue Trend</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $r['ref_year'] }}</span>
</div>
<div class="card-body" style="flex: 1;">
    <svg viewBox="0 0 330 170" style="width: 100%; height: 200px;">
        @foreach($gridLevels as $val)
            @php $gy = $bottom - ($val / $max) * ($bottom - $top); @endphp
            <line x1="{{ $left }}" y1="{{ $gy }}" x2="{{ $right }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1"/>
            <text x="{{ $left - 6 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="9" fill="#94a3b8">{{ $fmtAxis($val) }}</text>
        @endforeach
        <path d="{{ $areaPath }}" fill="rgba(232,75,30,0.10)"/>
        <polyline points="{{ $polyline }}" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
        @foreach($coords as $c)
            <circle cx="{{ $c[0] }}" cy="{{ $c[1] }}" r="3.5" fill="#fff" stroke="var(--primary)" stroke-width="2"/>
        @endforeach
        @foreach($r['months'] as $i => $m)
            <text x="{{ $coords[$i][0] }}" y="165" text-anchor="middle" font-size="9" fill="#94a3b8">{{ $m }}</text>
        @endforeach
    </svg>
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
