@extends('superadmin.layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Security Settings</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>System Settings</span>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.settings.company-profile') }}" class="sub-nav-tab">Company Profile</a>
    <a href="{{ route('superadmin.settings.prefix') }}" class="sub-nav-tab">Prefix Settings</a>
    <a href="{{ route('superadmin.settings.smtp') }}" class="sub-nav-tab">SMTP Settings</a>
    <a href="{{ route('superadmin.settings.backup') }}" class="sub-nav-tab">Backup Settings</a>
    <a href="{{ route('superadmin.settings.security') }}" class="sub-nav-tab active">Security Settings</a>
</div>

<form action="{{ route('superadmin.settings.security.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-user-clock"></i> Session Settings</div>
                <div class="form-group">
                    <label class="form-label">Auto Logout After Inactivity</label>
                    <select name="auto_logout" class="form-control">
                        @foreach(['15_minutes' => '15 Minutes', '30_minutes' => '30 Minutes', '1_hour' => '1 Hour', '2_hours' => '2 Hours'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('auto_logout', $security->auto_logout) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Password Rule</label>
                    <input type="text" class="form-control" value="Minimum 8 characters" disabled>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-user-shield"></i> Login Protection</div>
                <div class="form-group">
                    <label class="form-label">Failed Attempts Before Lock</label>
                    <input type="number" name="login_attempts_limit" class="form-control @error('login_attempts_limit') is-invalid @enderror" value="{{ old('login_attempts_limit', $security->loginAttemptsLimit()) }}" min="1" max="10" required>
                    @error('login_attempts_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Lock Duration</label>
                    <select name="account_lock_duration" class="form-control">
                        @foreach(['15_minutes' => '15 Minutes', '30_minutes' => '30 Minutes', '1_hour' => '1 Hour', '24_hours' => '24 Hours'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('account_lock_duration', $security->account_lock_duration) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
</form>

<div class="card" style="margin-top: 20px; margin-bottom: 40px;">
    <div class="card-body">
        <div class="section-title"><i class="fas fa-laptop"></i> Your Active Sessions</div>
        @forelse($sessions as $session)
            <div class="session-item">
                <div class="session-info">
                    <div class="session-icon {{ $session['icon'] }}"><i class="fab fa-{{ $session['icon'] }}"></i></div>
                    <div class="session-device">
                        <h4>{{ $session['device'] }} @if($session['current'])<span class="badge badge-success" style="font-size: 10px; margin-left: 8px;">Current</span>@endif</h4>
                        <p><i class="fas fa-network-wired" style="margin-right: 4px;"></i>IP: {{ $session['ip'] ?? '—' }} <span style="margin-left: 12px;"><i class="fas fa-clock" style="margin-right: 4px;"></i>{{ $session['last_active']->diffForHumans() }}</span></p>
                    </div>
                </div>
                @unless($session['current'])
                    <form action="{{ route('superadmin.settings.security.sessions.destroy', $session['id']) }}" method="POST" data-confirm="Sign this device out?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Revoke</button>
                    </form>
                @endunless
            </div>
        @empty
            <div class="empty-state"><div class="empty-state-title">No active sessions</div></div>
        @endforelse
    </div>
</div>
@endsection
