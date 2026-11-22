{{-- Signature hero tower: flat windows light up as payments arrive (animated by marketing.js). Optional $floorCount. --}}
@php
    $floors = range($floorCount ?? 8, 1);
    $flatAmounts = [1 => 3150, 2 => 2450, 3 => 2450, 4 => 3150];
    $billed = count($floors) * array_sum($flatAmounts);
@endphp
<div class="mk-scene" data-tower role="img" aria-label="Illustration: a residential tower whose windows light up as each flat pays its maintenance, with a running total of the month's collection">
    <div class="mk-skyline s1" aria-hidden="true"></div>
    <div class="mk-skyline s2" aria-hidden="true"></div>
    <div class="mk-skyline s3" aria-hidden="true"></div>

    <div class="mk-tower" aria-hidden="true">
        <div class="mk-roof">
            <span class="mk-tank"></span>
            <span class="mk-liftroom"></span>
            <span class="mk-tank"></span>
        </div>
        <div class="mk-floors">
            @foreach($floors as $floor)
                @foreach($flatAmounts as $flat => $amount)
                    @php
                        $flatNumber = 'B-'.$floor.'0'.$flat;
                        $isPaid = ($floor * 7 + $flat * 3) % 5 < 3;
                    @endphp
                    <div class="mk-flat {{ $isPaid ? 'is-paid' : '' }}" data-flat="{{ $flatNumber }}" data-amount="{{ $amount }}"><span>{{ $flatNumber }}</span></div>
                @endforeach
            @endforeach
        </div>
        <div class="mk-base">
            <span class="mk-sign">Gulmohar Heights · Wing B</span>
        </div>
    </div>

    <div class="mk-collect" aria-hidden="true">
        <div class="mk-collect-top">
            <span class="mk-collect-label">Maintenance collected</span>
            <span class="mk-collect-month">{{ now()->format('M Y') }}</span>
        </div>
        <div class="mk-collect-amount" data-collected>₹0</div>
        <div class="mk-collect-of">of ₹{{ number_format($billed) }} billed</div>
        <div class="mk-bar"><span data-bar></span></div>
        <div class="mk-collect-flats" data-count></div>
    </div>

    <div class="mk-toast" data-toast aria-hidden="true">
        <span class="mk-toast-icon"><i class="fas fa-indian-rupee-sign"></i></span>
        <span>
            <span class="mk-toast-title" data-toast-title></span>
            <span class="mk-toast-meta" data-toast-meta></span>
        </span>
    </div>
</div>
