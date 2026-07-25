@extends('auth.password-shell')

@section('title', 'Create password')

@section('content')
    <h1>Create your password</h1>
    <p class="lead">Choose at least eight characters. Once saved, this invitation link cannot be reused.</p>

    @if($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
        <label for="password">New password</label>
        <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        <button type="submit">Save password</button>
    </form>
@endsection
