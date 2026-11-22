{{-- Closing call to action on a dusk band. --}}
<section class="mk-dusk on-dusk mk-cta" aria-labelledby="cta-title">
    <div class="mk-container">
        <div class="mk-cta-windows" aria-hidden="true">
            @for($i = 0; $i < 12; $i++)<i></i>@endfor
        </div>
        <h2 class="mk-h2" id="cta-title">See your own society in it <em>before you decide</em></h2>
        <p class="mk-lead">We walk your committee through billing, accounts and the resident portal using your wings, flats and charges.</p>
        <div class="mk-hero-actions">
            <a href="{{ route('demo.create') }}" class="mk-btn mk-btn-primary">Request a demo <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('pricing') }}" class="mk-btn mk-btn-ghost">See pricing</a>
        </div>
    </div>
</section>
