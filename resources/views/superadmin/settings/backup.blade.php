@extends('superadmin.layouts.app')

@section('title', 'Backup Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Backup Settings</h1>
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

<div class="grid-2">
    <form action="{{ route('superadmin.settings.backup.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-cog"></i> Backup Schedule</div>
                <div class="form-group">
                    <label class="form-label">Backup Frequency</label>
                    <select name="backup_frequency" class="form-control">
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly (Mondays)', 'monthly' => 'Monthly (1st of the month)'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('backup_frequency', $backup->backup_frequency) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Backup Time</label>
                    <input type="time" name="backup_time" class="form-control @error('backup_time') is-invalid @enderror" value="{{ old('backup_time', $backup->scheduleTime()) }}" required>
                    @error('backup_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Keep Backups For</label>
                    <select name="retention_period" class="form-control">
                        @foreach(['7_days' => '7 Days', '14_days' => '14 Days', '30_days' => '30 Days', '60_days' => '60 Days', '90_days' => '90 Days'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('retention_period', $backup->retention_period) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Backup Location</label>
                    <input type="text" class="form-control" value="Server storage (storage/app/private/backups)" disabled>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px;">
                    <span style="font-size: 13px;">Email super admins after each backup</span>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_notification" value="1" @checked(old('email_notification', $backup->email_notification))>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-database"></i> Backup Items</div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach(['Database (members, billing, accounts, settings)' => 'backup_database', 'Uploaded Files & Documents' => 'backup_files', 'System Logs' => 'backup_logs'] as $label => $field)
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 13px;">{{ $label }}</span>
                            <label class="toggle-switch">
                                <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $backup->$field))>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    @endforeach
                    @error('backup_database')<div class="invalid-feedback" style="display: block;">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </form>

    <div class="card" style="align-self: start;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-history" style="color: var(--primary);"></i> Backup History</div>
            <form action="{{ route('superadmin.settings.backup.run') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-play"></i> Run Backup Now</button>
            </form>
        </div>
        <div class="card-body">
            @forelse($runs as $run)
                <div class="backup-item">
                    <div style="min-width: 0;">
                        <div style="font-size: 12px; font-weight: 500;"><i class="fas fa-calendar" style="color: var(--text-muted); margin-right: 6px;"></i>{{ ($run->started_at ?? $run->created_at)->format('d M Y \a\t h:i A') }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            {{ ucfirst($run->trigger) }}@if($run->triggeredBy) by {{ $run->triggeredBy->name }}@endif
                            @if($run->status === 'completed')
                                <span style="margin-left: 10px;"><i class="fas fa-database" style="margin-right: 4px;"></i>{{ $run->sizeLabel() }}</span>
                                <span style="margin-left: 10px;"><i class="fas fa-clock" style="margin-right: 4px;"></i>{{ $run->durationLabel() }}</span>
                            @endif
                        </div>
                        @if($run->status === 'failed' && $run->error)
                            <div style="font-size: 11px; color: var(--danger); margin-top: 2px;">{{ $run->error }}</div>
                        @endif
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;">
                        <span class="status-badge {{ $run->statusBadgeClass() }}">{{ ucfirst($run->status) }}</span>
                        @if($run->isDownloadable())
                            <a href="{{ route('superadmin.settings.backup.download', $run) }}" class="btn btn-secondary btn-sm" title="Download"><i class="fas fa-download"></i></a>
                        @endif
                        @unless(in_array($run->status, ['queued', 'running'], true))
                            <form action="{{ route('superadmin.settings.backup.destroy', $run) }}" method="POST" data-confirm="Delete this backup?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="empty-state"><div class="empty-state-title">No backups yet</div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
