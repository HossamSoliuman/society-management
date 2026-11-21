@extends('auth.password-shell')

@section('title', 'Join '.$society->name)

@section('content')
<style>
    select, textarea { width: 100%; border: 1px solid var(--line); border-radius: 9px; padding: 12px 13px; font: 14px Manrope; outline: none; background: white; }
    textarea { resize: vertical; min-height: 80px; }
    select:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(232,75,30,.1); }
    .pair { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 480px) { .pair { grid-template-columns: 1fr; } }
</style>

<h1>Join {{ $society->name }}</h1>

@if(session('status'))
    <div class="notice">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="errors">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('join.store', $code) }}" enctype="multipart/form-data">
    @csrf

    <label for="request_type">Request</label>
    <select id="request_type" name="request_type" required>
        @foreach(\App\Models\MemberRequest::TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('request_type', 'join') === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <div class="pair">
        <div>
            <label for="member_type">Joining as</label>
            <select id="member_type" name="member_type" required>
                @foreach(\App\Models\MemberRequest::MEMBER_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('member_type', 'owner') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="unit_number">Flat / unit number</label>
            <input id="unit_number" name="unit_number" value="{{ old('unit_number') }}" placeholder="e.g. A-101" required>
        </div>
    </div>

    <label for="name">Full name</label>
    <input id="name" name="name" value="{{ old('name') }}" required>

    <div class="pair">
        <div>
            <label for="mobile">Mobile</label>
            <input id="mobile" name="mobile" value="{{ old('mobile') }}" required>
        </div>
        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}">
        </div>
    </div>

    <label for="move_in_date">Moving in on</label>
    <input id="move_in_date" type="date" name="move_in_date" value="{{ old('move_in_date') }}">

    <label for="document">Proof (sale deed, agreement or ID)</label>
    <input id="document" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">

    <label for="message">Message to the office</label>
    <textarea id="message" name="message">{{ old('message') }}</textarea>

    <button type="submit">Send Request</button>
</form>
@endsection
