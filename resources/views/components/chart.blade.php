@props([
    'type' => 'line',
    'labels' => [],
    'datasets' => [],
    'height' => 220,
    'size' => null,
    'format' => 'number',
    'legend' => false,
    'stacked' => false,
    'horizontal' => false,
    'cutout' => null,
    'centerValue' => null,
    'centerLabel' => null,
    'label' => null,
])

@php
    /**
     * Chart.js chart rendered by public/js/charts.js.
     *
     * Datasets: [['label' => 'Collected', 'data' => [..], 'color' => '#hex'|'var(--primary)', 'fill' => bool, 'dashed' => bool, 'type' => 'bar'|'line', 'stack' => 'a']]
     * Doughnut / pie: a single dataset with 'data' and a matching 'colors' list.
     */
    $circular = in_array($type, ['doughnut', 'pie'], true);
    $config = array_filter([
        'type' => $type,
        'labels' => array_values($labels),
        'datasets' => array_map(function (array $dataset): array {
            $dataset['data'] = array_map(fn ($value) => round((float) $value, 2), array_values($dataset['data'] ?? []));

            return $dataset;
        }, array_values($datasets)),
        'format' => $format,
        'legend' => $legend,
        'stacked' => $stacked,
        'horizontal' => $horizontal,
        'cutout' => $cutout,
    ], fn ($value) => $value !== null && $value !== false);
    $box = $circular
        ? 'width: '.($size ?? 160).'px; height: '.($size ?? 160).'px;'
        : 'height: '.$height.'px;';
@endphp

<div {{ $attributes->class([$circular ? 'pie-chart-container' : 'chart-box']) }} style="{{ $box }}">
    <canvas data-chart="{{ json_encode($config) }}" role="img" aria-label="{{ $label ?? 'Chart' }}"></canvas>
    @if($circular && ! is_null($centerValue))
        <div class="pie-chart-center">
            <div class="value">{{ $centerValue }}</div>
            @if($centerLabel)
                <div class="label">{{ $centerLabel }}</div>
            @endif
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
        <script src="{{ asset('js/charts.js') }}?v={{ filemtime(public_path('js/charts.js')) }}"></script>
    @endpush
@endonce
