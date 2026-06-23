@extends('superadmin.layouts.app')

@section('title', 'SMTP Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">SMTP Settings</h1>
            <p class="page-subtitle">Configure SMTP settings for email notifications.</p>
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
    <a href="{{ route('superadmin.settings.smtp') }}" class="sub-nav-tab active">SMTP Settings</a>
    <a href="{{ route('superadmin.settings.backup') }}" class="sub-nav-tab">Backup Settings</a>
    <a href="{{ route('superadmin.settings.security') }}" class="sub-nav-tab">Security Settings</a>
</div>

<form action="{{ route('superadmin.settings.smtp.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-server"></i> SMTP Server Configuration</div>
                <div class="form-group">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control" value="{{ $smtp->smtp_host ?? '' }}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="{{ $smtp->smtp_port ?? 587 }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Encryption</label>
                        <select name="encryption" class="form-control">
                            <option value="STARTTLS" {{ ($smtp->encryption ?? '') == 'STARTTLS' ? 'selected' : '' }}>STARTTLS</option>
                            <option value="SSL" {{ ($smtp->encryption ?? '') == 'SSL' ? 'selected' : '' }}>SSL</option>
                            <option value="TLS" {{ ($smtp->encryption ?? '') == 'TLS' ? 'selected' : '' }}>TLS</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Authentication</label>
                    <select name="authentication" class="form-control">
                        <option value="Login" selected>Login</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" value="{{ $smtp->smtp_username ?? '' }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control" value="{{ $smtp->smtp_password ?? '' }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-envelope"></i> Email Settings</div>
                <div class="form-group">
                    <label class="form-label">From Email</label>
                    <input type="email" name="from_email" class="form-control" value="{{ $smtp->from_email ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">From Name</label>
                    <input type="text" name="from_name" class="form-control" value="{{ $smtp->from_name ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Reply-To Email</label>
                    <input type="email" name="reply_to_email" class="form-control" value="{{ $smtp->reply_to_email ?? '' }}">
                </div>

                <div class="section-title" style="margin-top: 24px;"><i class="fas fa-toggle-on"></i> Additional Settings</div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach(['Enable SSL/TLS' => 'enable_ssl_tls', 'Enable Email Logging' => 'enable_email_logging'] as $label => $field)
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px;">{{ $label }}</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="{{ $field }}" value="1" {{ ($smtp->$field ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; padding-bottom: 40px;">
        <button type="button" class="btn btn-outline-primary"><i class="fas fa-paper-plane"></i> Send Test Email</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
</form>
@endsection
