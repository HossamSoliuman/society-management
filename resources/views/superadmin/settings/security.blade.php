@extends('superadmin.layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Security Settings</h1>
            <p class="page-subtitle">Configure security policies for the platform.</p>
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
                <div class="section-title"><i class="fas fa-lock"></i> Password Policy</div>
                <div class="form-group">
                    <label class="form-label">Minimum Password Length</label>
                    <input type="number" name="min_password_length" class="form-control" value="{{ $security->min_password_length ?? 8 }}" min="4" max="32">
                </div>
                <div class="form-group">
                    <label class="form-label">Password Expiry</label>
                    <select name="password_expiry" class="form-control">
                        @foreach(['30_days' => '30 Days', '60_days' => '60 Days', '90_days' => '90 Days', '180_days' => '180 Days', 'never' => 'Never'] as $val => $label)
                            <option value="{{ $val }}" {{ ($security->password_expiry ?? '90_days') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
                    @foreach(['Require Uppercase' => 'require_uppercase', 'Require Lowercase' => 'require_lowercase', 'Require Numbers' => 'require_number', 'Require Special Characters' => 'require_special'] as $label => $field)
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px;">{{ $label }}</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="{{ $field }}" value="1" {{ ($security->$field ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-shield-alt"></i> Two-Factor Authentication</div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px;">Enable 2FA</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_2fa" value="1" {{ ($security->enable_2fa ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">2FA for Super Admin</label>
                    <select name="2fa_for_super_admin" class="form-control">
                        <option value="required" {{ ($security->{'2fa_for_super_admin'} ?? 'required') == 'required' ? 'selected' : '' }}>Required</option>
                        <option value="optional" {{ ($security->{'2fa_for_super_admin'} ?? '') == 'optional' ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">2FA for Others</label>
                    <select name="2fa_for_others" class="form-control">
                        <option value="required" {{ ($security->{'2fa_for_others'} ?? '') == 'required' ? 'selected' : '' }}>Required</option>
                        <option value="optional" {{ ($security->{'2fa_for_others'} ?? 'optional') == 'optional' ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>

                <div class="section-title" style="margin-top: 24px;"><i class="fas fa-user-clock"></i> Session Settings</div>
                <div class="form-group">
                    <label class="form-label">Auto Logout</label>
                    <select name="auto_logout" class="form-control">
                        @foreach(['15_minutes' => '15 Minutes', '30_minutes' => '30 Minutes', '1_hour' => '1 Hour', '2_hours' => '2 Hours'] as $val => $label)
                            <option value="{{ $val }}" {{ ($security->auto_logout ?? '30_minutes') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-body">
            <div class="section-title"><i class="fas fa-user-shield"></i> Login Protection</div>
            <div class="grid-2">
                <div>
                    <div class="form-group">
                        <label class="form-label">Login Attempts Limit</label>
                        <input type="number" name="login_attempts_limit" class="form-control" value="{{ $security->login_attempts_limit ?? 5 }}" min="1" max="10">
                        <div class="form-text">Number of failed login attempts before account lockout</div>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label class="form-label">Account Lock Duration</label>
                        <select name="account_lock_duration" class="form-control">
                            @foreach(['15_minutes' => '15 Minutes', '30_minutes' => '30 Minutes', '1_hour' => '1 Hour', '24_hours' => '24 Hours'] as $val => $label)
                                <option value="{{ $val }}" {{ ($security->account_lock_duration ?? '30_minutes') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Duration for which account remains locked after failed attempts</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-body">
            <div class="section-title"><i class="fas fa-laptop"></i> Active Sessions</div>
            @php $sessions = [['device' => 'Chrome on Windows 10', 'location' => 'Ahmedabad, Gujarat, India', 'ip' => '192.168.1.105', 'icon' => 'chrome', 'current' => true], ['device' => 'Safari on macOS', 'location' => 'Ahmedabad, Gujarat, India', 'ip' => '192.168.1.106', 'icon' => 'safari', 'current' => false], ['device' => 'Firefox on Ubuntu', 'location' => 'Mumbai, Maharashtra, India', 'ip' => '203.192.12.45', 'icon' => 'firefox', 'current' => false], ['device' => 'Edge on Windows 11', 'location' => 'Ahmedabad, Gujarat, India', 'ip' => '192.168.1.107', 'icon' => 'edge', 'current' => false]]; @endphp
            @foreach($sessions as $session)
            <div class="session-item">
                <div class="session-info">
                    <div class="session-icon {{ $session['icon'] }}"><i class="fab fa-{{ $session['icon'] }}"></i></div>
                    <div class="session-device">
                        <h4>{{ $session['device'] }} {!! $session['current'] ? '<span class="badge badge-success" style="font-size: 10px; margin-left: 8px;">Current</span>' : '' !!}</h4>
                        <p><i class="fas fa-map-marker-alt" style="margin-right: 4px;"></i>{{ $session['location'] }} <span style="margin-left: 12px;"><i class="fas fa-network-wired" style="margin-right: 4px;"></i>IP: {{ $session['ip'] }}</span></p>
                    </div>
                </div>
                @if(!$session['current'])
                    <button class="btn btn-outline-danger btn-sm">Revoke</button>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding-bottom: 40px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
</form>
@endsection
