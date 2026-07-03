{{-- @param array<int, array{label:string,value:string,sub:string,icon:string,color:string}> $stats --}}
<div class="stats-grid" style="grid-template-columns: repeat({{ count($stats) }}, 1fr);">
    @foreach($stats as $s)
        <div class="stat-card">
            <div class="stat-icon {{ $s['color'] }}"><i class="fas {{ $s['icon'] }}"></i></div>
            <div class="stat-info">
                <div class="stat-label">{{ $s['label'] }}</div>
                <div class="stat-value">{!! $s['value'] !!}</div>
                <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $s['sub'] }}</span></div>
            </div>
        </div>
    @endforeach
</div>
