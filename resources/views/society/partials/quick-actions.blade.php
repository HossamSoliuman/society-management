@php
    /**
     * Right-rail "Quick Actions" card (icon + title + description rows).
     *
     * @var string $title              optional heading (default "Quick Actions")
     * @var array  $items              each: ['icon' => 'fa-plus', 'label' => '', 'desc' => '', 'url' => '', 'color' => 'orange']
     */
    $title = $title ?? 'Quick Actions';
    $tints = [
        'primary' => ['var(--primary-light)', 'var(--primary)'],
        'orange' => ['var(--orange-light)', 'var(--orange)'],
        'blue' => ['var(--info-light)', 'var(--info)'],
        'green' => ['var(--success-light)', 'var(--success)'],
        'red' => ['var(--danger-light)', 'var(--danger)'],
        'purple' => ['var(--purple-light)', 'var(--purple)'],
        'teal' => ['var(--teal-light)', 'var(--teal)'],
        'pink' => ['var(--pink-light)', 'var(--pink)'],
        'yellow' => ['var(--warning-light)', 'var(--warning)'],
        'gray' => ['var(--gray-100)', 'var(--gray-500)'],
    ];
@endphp
<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">{{ $title }}</div>
        @foreach($items as $item)
            @php [$bg, $fg] = $tints[$item['color'] ?? 'primary'] ?? $tints['primary']; @endphp
            <a href="{{ $item['url'] ?? '#' }}" class="qa-item">
                <span class="qa-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $item['icon'] }}"></i></span>
                <span>
                    <span class="qa-title" style="display: block;">{{ $item['label'] }}</span>
                    <span class="qa-desc">{{ $item['desc'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
