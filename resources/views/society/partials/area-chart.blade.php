@php
    /**
     * Multi-series area/line chart (inline SVG).
     *
     * @var array  $series   [['points' => [float...], 'stroke' => '#hex', 'fill' => 'rgba()'|null], ...]
     * @var array  $labels   x-axis labels (same count as each series' points)
     * @var float  $max      y-axis maximum
     * @var array  $yTicks   optional ['₹2L' => 200000, ...] grid lines
     * @var int    $height   optional px height (default 220)
     */
    $height = $height ?? 220;
    $yTicks = $yTicks ?? [];
    $left = 40;
    $right = 320;
    $top = 12;
    $bottom = 150;
    $span = $right - $left;
    $max = $max ?: 1;

    $plot = function (array $points) use ($left, $span, $bottom, $top, $max) {
        $count = count($points);
        $coords = [];
        foreach ($points as $i => $val) {
            $x = $left + ($count > 1 ? $i * ($span / ($count - 1)) : 0);
            $y = $bottom - ($val / $max) * ($bottom - $top);
            $coords[] = [round($x, 1), round($y, 1)];
        }

        return $coords;
    };
@endphp
<svg viewBox="0 0 340 170" style="width: 100%; height: {{ $height }}px;">
    @foreach($yTicks as $label => $val)
        @php $gy = $bottom - ($val / $max) * ($bottom - $top); @endphp
        <line x1="{{ $left }}" y1="{{ $gy }}" x2="{{ $right }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1"/>
        <text x="{{ $left - 6 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="9" fill="#94a3b8">{{ $label }}</text>
    @endforeach

    @foreach($series as $s)
        @php
            $coords = $plot($s['points']);
            $polyline = implode(' ', array_map(fn ($c) => $c[0].','.$c[1], $coords));
        @endphp
        @if(! empty($s['fill']))
            @php $areaPath = 'M'.$coords[0][0].','.$bottom.' L'.implode(' L', array_map(fn ($c) => $c[0].','.$c[1], $coords)).' L'.end($coords)[0].','.$bottom.' Z'; @endphp
            <path d="{{ $areaPath }}" fill="{{ $s['fill'] }}"/>
        @endif
        <polyline points="{{ $polyline }}" fill="none" stroke="{{ $s['stroke'] }}" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
    @endforeach

    @foreach($labels as $i => $m)
        @php
            $count = count($labels);
            $x = $left + ($count > 1 ? $i * ($span / ($count - 1)) : 0);
        @endphp
        <text x="{{ round($x, 1) }}" y="165" text-anchor="middle" font-size="9" fill="#94a3b8">{{ $m }}</text>
    @endforeach
</svg>
