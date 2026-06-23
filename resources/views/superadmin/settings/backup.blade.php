@extends('superadmin.layouts.app')

@section('title', 'Backup Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Backup Settings</h1>
            <p class="page-subtitle">Configure automated backup settings.</p>
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
    <a href="{{ route('superadmin.settings.backup') }}" class="sub-nav-tab active">Backup Settings</a>
    <a href="{{ route('superadmin.settings.security') }}" class="sub-nav-tab">Security Settings</a>
</div>

<form action="{{ route('superadmin.settings.backup.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-cog"></i> Backup Configuration</div>
                <div class="form-group">
                    <label class="form-label">Backup Frequency</label>
                    <select name="backup_frequency" class="form-control">
                        <option value="daily" {{ ($backup->backup_frequency ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ ($backup->backup_frequency ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ ($backup->backup_frequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Backup Time</label>
                    <input type="time" name="backup_time" class="form-control" value="{{ $backup->backup_time ?? '02:00' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Retention Period</label>
                    <select name="retention_period" class="form-control">
                        <option value="7_days" {{ ($backup->retention_period ?? '') == '7_days' ? 'selected' : '' }}>7 Days</option>
                        <option value="14_days" {{ ($backup->retention_period ?? '') == '14_days' ? 'selected' : '' }}>14 Days</option>
                        <option value="30_days" {{ ($backup->retention_period ?? '30_days') == '30_days' ? 'selected' : '' }}>30 Days</option>
                        <option value="60_days" {{ ($backup->retention_period ?? '') == '60_days' ? 'selected' : '' }}>60 Days</option>
                        <option value="90_days" {{ ($backup->retention_period ?? '') == '90_days' ? 'selected' : '' }}>90 Days</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Backup Location</label>
                    <select name="backup_location" class="form-control">
                        <option value="local" {{ ($backup->backup_location ?? '') == 'local' ? 'selected' : '' }}>Local Storage</option>
                        <option value="aws_s3" {{ ($backup->backup_location ?? 'aws_s3') == 'aws_s3' ? 'selected' : '' }}>AWS S3</option>
                        <option value="google_drive" {{ ($backup->backup_location ?? '') == 'google_drive' ? 'selected' : '' }}>Google Drive</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px;">
                    <span style="font-size: 13px;">Email Notification</span>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_notification" value="1" {{ ($backup->email_notification ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-database"></i> Backup Items</div>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach(['Database' => 'backup_database', 'User Data' => 'backup_user_data', 'Files & Documents' => 'backup_files', 'System Logs' => 'backup_logs', 'Settings & Configurations' => 'backup_settings'] as $label => $field)
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 13px;"><i class="fas fa-check-circle" style="color: var(--success); margin-right: 8px;"></i>{{ $label }}</span>
                            <label class="toggle-switch">
                                <input type="checkbox" name="{{ $field }}" value="1" {{ ($backup->$field ?? true) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-history" style="color: var(--primary);"></i> Backup History</div></div>
                <div class="card-body">
                    @php $backups = [['date' => '24 May 2025 at 02:00 AM', 'size' => '156.3 MB', 'status' => 'Completed', 'time' => '24s'], ['date' => '23 May 2025 at 02:00 AM', 'size' => '154.8 MB', 'status' => 'Completed', 'time' => '22s'], ['date' => '22 May 2025 at 02:00 AM', 'size' => '153.2 MB', 'status' => 'Completed', 'time' => '25s']]; @endphp
                    @foreach($backups as $b)
                    <div class="backup-item">
                        <div>
                            <div style="font-size: 12px; font-weight: 500;"><i class="fas fa-calendar" style="color: var(--text-muted); margin-right: 6px;"></i>{{ $b['date'] }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-database" style="margin-right: 4px;"></i>{{ $b['size'] }} <span style="margin-left: 12px;"><i class="fas fa-clock" style="margin-right: 4px;"></i>{{ $b['time'] }}</span></div>
                        </div>
                        <span class="status-badge {{ strtolower($b['status']) }}">{{ $b['status'] }}</span>
                    </div>
                    @endforeach
                    <div style="margin-top: 12px;"><a href="#" class="btn btn-outline-primary btn-sm" style="width: 100%;"><i class="fas fa-play"></i> Run Manual Backup Now</a></div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding-bottom: 40px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
</form>
@endsection
