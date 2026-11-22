<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · Society Management</title>
    <meta name="description" content="@yield('description')">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title') · Society Management">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:image" content="{{ asset('images/marketing/dashboard.webp') }}">
    <meta name="theme-color" content="#13163A">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Figtree:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
</head>
<body>
    <a href="#main" class="mk-skip">Skip to content</a>

    @php
        $homeRoute = auth()->user()?->homeRouteName();
    @endphp

    <header class="mk-header" data-header>
        <div class="mk-container mk-header-inner">
            <a href="{{ route('home') }}" class="mk-brand" aria-label="Society Management home">
                <span class="mk-brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-6h6v6"/>
                        <path d="M9 9h1v1H9zM14 9h1v1h-1z"/>
                    </svg>
                </span>
                <span class="mk-brand-name">Society Management</span>
            </a>

            <nav class="mk-nav" aria-label="Main">
                <a href="{{ route('features') }}" @if(request()->routeIs('features')) aria-current="page" @endif>Features</a>
                <a href="{{ route('pricing') }}" @if(request()->routeIs('pricing')) aria-current="page" @endif>Pricing</a>
                <a href="{{ route('demo.create') }}" @if(request()->routeIs('demo.*')) aria-current="page" @endif>Request a demo</a>
            </nav>

            <div class="mk-header-actions">
                @if($homeRoute)
                    <a href="{{ route($homeRoute) }}" class="mk-btn mk-btn-primary mk-btn-sm">Open dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="mk-signin">Sign in</a>
                    <a href="{{ route('demo.create') }}" class="mk-btn mk-btn-primary mk-btn-sm">Request a demo</a>
                @endif
            </div>

            <button type="button" class="mk-menu-btn" data-menu-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="Open menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="mk-mobile-nav" id="mobile-nav" aria-label="Mobile">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('features') }}">Features</a>
            <a href="{{ route('pricing') }}">Pricing</a>
            @if($homeRoute)
                <a href="{{ route($homeRoute) }}" class="mk-btn mk-btn-primary">Open dashboard</a>
            @else
                <a href="{{ route('login') }}">Sign in</a>
                <a href="{{ route('demo.create') }}" class="mk-btn mk-btn-primary">Request a demo</a>
            @endif
        </nav>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <footer class="mk-footer">
        <div class="mk-container">
            <div class="mk-footer-grid">
                <div>
                    <a href="{{ route('home') }}" class="mk-brand">
                        <span class="mk-brand-mark">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-6h6v6"/>
                                <path d="M9 9h1v1H9zM14 9h1v1h-1z"/>
                            </svg>
                        </span>
                        <span class="mk-brand-name">Society Management</span>
                    </a>
                    <p class="mk-footer-about">Billing, accounts, gate and resident services for housing societies, RWAs and apartment associations.</p>
                </div>
                <div>
                    <h2>Product</h2>
                    <ul>
                        <li><a href="{{ route('features') }}">Features</a></li>
                        <li><a href="{{ route('pricing') }}">Pricing</a></li>
                        <li><a href="{{ route('demo.create') }}">Request a demo</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Account</h2>
                    <ul>
                        <li><a href="{{ route('login') }}">Sign in</a></li>
                        <li><a href="{{ route('password.request') }}">Reset password</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Contact</h2>
                    <ul class="mk-footer-contact">
                        @if($company?->email)
                            <li><i class="fas fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $company->email }}">{{ $company->email }}</a></li>
                        @endif
                        @if($company?->phone)
                            <li><i class="fas fa-phone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->phone) }}">{{ $company->phone }}</a></li>
                        @endif
                        @if($company?->address)
                            <li><i class="fas fa-location-dot" aria-hidden="true"></i><span>{{ $company->address }}</span></li>
                        @endif
                        <li><i class="fas fa-calendar-check" aria-hidden="true"></i><a href="{{ route('demo.create') }}">Book a walkthrough</a></li>
                    </ul>
                </div>
            </div>
            <div class="mk-footer-bottom">
                <span>© {{ now()->year }} {{ $company?->company_name ?: 'Society Management' }}</span>
                <span>Made for housing societies in India</span>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}" defer></script>
</body>
</html>
