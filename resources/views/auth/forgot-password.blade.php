@extends('auth.password-shell')

@section('title', 'Reset password')

@section('content')
    <h1>Recover access</h1>
    <p class="lead">Enter your account email. We will send a secure, time-limited link to create a new password.</p>

    @if(session('status')) <div class="notice">{{ session('status') }}</div> @endif
    @if($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        <button type="submit">Send secure link</button>
    </form>
    <a class="back" href="{{ route('login') }}">Back to sign in</a>
@endsection
