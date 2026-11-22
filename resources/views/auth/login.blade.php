<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · Society Management</title>
    <x-favicons />
    <meta name="theme-color" content="#13163A">
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Figtree:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
</head>
<body>
    <div class="mk-dusk on-dusk mk-auth">
        <header class="mk-container mk-auth-header">
            <a href="{{ route('home') }}" class="mk-brand" aria-label="Society Management home">
                <span class="mk-brand-mark">
                    <x-brand-mark />
                </span>
                <span class="mk-brand-name">Society Management</span>
            </a>
            <a href="{{ route('home') }}" class="mk-auth-back" aria-label="Back to site"><i class="fas fa-arrow-left" aria-hidden="true"></i><span>Back to site</span></a>
        </header>

        <main class="mk-container mk-auth-center">
            <div class="mk-auth-main">
                <h1 class="mk-display">Welcome <em>back.</em></h1>

                <div class="mk-form-card mk-auth-card">
                    @if(session('status'))
                        <div class="mk-notice" role="status">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="mk-alert" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mk-auth-form">
                        @csrf
                        <div class="mk-field">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="mk-input" autocomplete="username" required autofocus>
                        </div>

                        <div class="mk-field">
                            <label for="password">Password</label>
                            <div class="mk-password">
                                <input type="password" id="password" name="password" class="mk-input" autocomplete="current-password" required>
                                <button type="button" class="mk-password-toggle" data-password-toggle aria-controls="password" aria-label="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mk-auth-row">
                            <label class="mk-check">
                                <input type="checkbox" name="remember" @checked(old('remember'))>
                                <span>Remember me</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="mk-auth-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="mk-btn mk-btn-primary mk-btn-block">Sign in <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                    </form>

                    <p class="mk-auth-alt">New to Society Management? <a href="{{ route('demo.create') }}" class="mk-auth-link">Request a demo</a></p>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}" defer></script>
</body>
</html>
