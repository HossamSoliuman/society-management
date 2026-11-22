@extends('auth.password-shell')

@section('title', 'Access blocked')

@section('content')
    <h1>Access blocked</h1>
    <p class="lead">{{ $exception->getMessage() ?: 'You do not have permission to open this page.' }}</p>

    @if(str_contains(strtolower((string) $exception->getMessage()), 'subscription'))
        <div class="notice">Contact the platform team to renew the subscription. Access comes back as soon as it is renewed.</div>
    @endif

    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Sign out</button>
        </form>
        <a class="back" href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">Go back</a>
    @else
        <a class="back" href="{{ route('login') }}">Sign in</a>
    @endauth
@endsection
