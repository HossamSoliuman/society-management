@php
    $bannerSociety = auth()->user()?->society;
    $bannerEnd = $bannerSociety?->subscription_end_date;
    $bannerDays = $bannerEnd ? (int) now()->startOfDay()->diffInDays($bannerEnd->copy()->startOfDay(), false) : null;
    $bannerAccessUntil = $bannerEnd?->copy()->addDays((int) $bannerSociety->grace_period_days);
@endphp
@if($bannerEnd && ($bannerDays <= 30 || $bannerSociety->subscription_status === 'expiring_soon'))
    <div class="alert {{ $bannerDays < 0 ? 'alert-danger' : 'alert-warning' }}">
        <i class="fas fa-triangle-exclamation"></i>
        <span>
            @if($bannerDays < 0)
                Your subscription ended on {{ $bannerEnd->format('d M Y') }}. Access stops after {{ $bannerAccessUntil->format('d M Y') }} unless it is renewed.
            @elseif($bannerDays === 0)
                Your subscription ends today.
            @else
                Your subscription ends in {{ $bannerDays }} {{ \Illuminate\Support\Str::plural('day', $bannerDays) }} on {{ $bannerEnd->format('d M Y') }}.
            @endif
            <a href="{{ route('society.subscription.index') }}" style="font-weight: 600;">View subscription</a>
        </span>
    </div>
@endif
