@php
    /**
     * @var array  $segments     [['value' => float, 'color' => '#hex'], ...]
     * @var string $centerValue
     * @var string $centerLabel
     * @var int    $size          optional px size (default 160)
     * @var int    $stroke        optional stroke width (default 13)
     */
    $size = $size ?? 160;
    $stroke = $stroke ?? 13;
    $circumference = 251.2; // 2 * pi * r where r = 40
    $total = collect($segments)->sum('value') ?: 1;
    $offset = 0;
@endphp
<div class="pie-chart-container" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
        <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="{{ $stroke }}"/>
        @foreach($segments as $segment)
            @php
                $pct = ($segment['value'] / $total) * $circumference;
            @endphp
            <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $segment['color'] }}" stroke-width="{{ $stroke }}"
                stroke-dasharray="{{ $pct }} {{ $circumference - $pct }}"
                stroke-dashoffset="-{{ $offset }}" stroke-linecap="butt"/>
            @php $offset += $pct; @endphp
        @endforeach
    </svg>
    <div class="pie-chart-center">
        <div class="value">{!! $centerValue !!}</div>
        <div class="label">{{ $centerLabel }}</div>
    </div>
</div>
