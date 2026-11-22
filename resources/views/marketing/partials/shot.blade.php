{{-- Product screenshot in a browser frame. Expects $src, $path, $alt. --}}
<figure class="mk-shot">
    <div class="mk-shot-bar" aria-hidden="true">
        <span class="mk-shot-dots"><i></i><i></i><i></i></span>
        <span class="mk-shot-url"><i class="fas fa-lock"></i>{{ $path }}</span>
    </div>
    <img src="{{ asset('images/marketing/'.$src) }}" alt="{{ $alt }}" width="2160" height="{{ $height ?? 1350 }}" loading="{{ $loading ?? 'lazy' }}" decoding="async">
</figure>
