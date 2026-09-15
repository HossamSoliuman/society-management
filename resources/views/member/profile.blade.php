@extends('member.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Profile</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>My Profile</span>
            </div>
        </div>
    </div>
</div>

<div class="content-grid" style="grid-template-columns: 1fr 1fr;">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                    <div class="avatar" style="width: 64px; height: 64px;">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=FEE8E0&color=E84B1E&size=128" alt="">
                    </div>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700;">{{ $member->name }}</h2>
                        <div style="font-size: 13px; color: var(--text-muted);">{{ $member->typeLabel() }} · {{ $society->name }}</div>
                    </div>
                </div>

                @php
                    $rows = [
                        ['fa-door-open', 'Flat / Unit', $member->flat_unit],
                        ['fa-building', 'Tower / Wing', $member->tower_wing],
                        ['fa-city', 'Units', $member->units->pluck('unit_number')->implode(', ')],
                        ['fa-calendar', 'Member since', optional($member->join_date)->format('d M Y')],
                    ];
                @endphp
                @foreach($rows as [$icon, $label, $value])
                    <div class="detail-row">
                        <div class="detail-row-icon"><i class="fas {{ $icon }}"></i></div>
                        <div class="detail-row-label">{{ $label }}</div>
                        <div class="detail-row-value">{{ $value ?: '—' }}</div>
                    </div>
                @endforeach
                <div class="form-text" style="margin-top: 12px;">Flat, tower and membership details are maintained by the society office. Raise a request if something is wrong.</div>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header"><h3 class="card-title">Contact Details</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('member.profile.update') }}">
                    @csrf @method('PUT')
                    @if($errors->hasAny(['name', 'mobile', 'email']))
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Full name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required maxlength="255">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $member->mobile) }}" maxlength="20">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email (login) <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required maxlength="255">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Change Password</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('member.profile.password') }}">
                    @csrf @method('PUT')
                    @if($errors->hasAny(['current_password', 'new_password']))
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('current_password') ?: $errors->first('new_password') }}</div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Current password <span class="required">*</span></label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New password <span class="required">*</span></label>
                        <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                        <div class="form-text">At least 8 characters.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm new password <span class="required">*</span></label>
                        <input type="password" name="new_password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
