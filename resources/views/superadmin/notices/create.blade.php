@extends('superadmin.layouts.app')

@section('title', 'Create New Notice')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create New Notice</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('superadmin.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Communication</span>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('superadmin.notices.index') }}">Notice Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Create New Notice</span>
            </div>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.notices.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Notice List</a>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('superadmin.notices.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        {{-- Left column --}}
        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-address-card"></i> Notice Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Notice Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Enter notice title" value="{{ old('title') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notice Type <span class="required">*</span></label>
                            <select name="notice_type" class="form-control" required>
                                <option value="" disabled @selected(! old('notice_type'))>Select notice type</option>
                                @foreach(['general' => 'General', 'maintenance' => 'Maintenance', 'billing' => 'Billing', 'event' => 'Event'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('notice_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Priority <span class="required">*</span></label>
                            <select name="priority" class="form-control" required>
                                <option value="" disabled @selected(! old('priority'))>Select priority</option>
                                @foreach(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Short Description (Optional)</label>
                            <input type="text" name="short_description" class="form-control" placeholder="Enter short description" value="{{ old('short_description') }}">
                            <small style="color: var(--text-muted);">Brief summary of the notice (optional)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attach Document (Optional)</label>
                        <div class="upload-dropzone" style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 28px; text-align: center; color: var(--text-muted);">
                            <i class="fas fa-cloud-arrow-up" style="font-size: 22px;"></i>
                            <div style="margin-top: 8px;">Drag &amp; drop files here or click <span style="color: var(--primary);">to browse</span></div>
                            <div style="font-size: 11px;">PDF, JPG, PNG up to 5MB</div>
                        </div>
                        <input type="hidden" name="attach_path" value="{{ old('attach_path') }}">
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-file-lines"></i> Notice Content</div>
                    <div class="form-group">
                        <label class="form-label">Notice Content <span class="required">*</span></label>
                        <textarea name="content" class="form-control" rows="8" placeholder="Write your notice content here..." required>{{ old('content') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-gear"></i> Additional Options</div>
                    <div class="form-row-3">
                        <label class="setting-toggle-row" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer;">
                            <span class="setting-text">
                                <span class="label"><i class="fas fa-envelope"></i> Send Email Notification</span>
                                <span class="help">Send email to selected audience</span>
                            </span>
                            <input type="checkbox" name="send_email" value="1" @checked(old('send_email'))>
                        </label>
                        <label class="setting-toggle-row" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer;">
                            <span class="setting-text">
                                <span class="label"><i class="fas fa-comment"></i> Send SMS Notification</span>
                                <span class="help">Send SMS to selected audience</span>
                            </span>
                            <input type="checkbox" name="send_sms" value="1" @checked(old('send_sms'))>
                        </label>
                        <label class="setting-toggle-row" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer;">
                            <span class="setting-text">
                                <span class="label"><i class="fas fa-circle-check"></i> Require Acknowledgement</span>
                                <span class="help">Members must acknowledge this notice</span>
                            </span>
                            <input type="checkbox" name="require_acknowledgement" value="1" @checked(old('require_acknowledgement'))>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-calendar-days"></i> Publish Settings</div>

                    <div class="form-group">
                        <label class="form-label">Publish Date &amp; Time <span class="required">*</span></label>
                        <input type="datetime-local" name="publish_at" class="form-control" value="{{ old('publish_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expiry Date &amp; Time (Optional)</label>
                        <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                        <small style="color: var(--text-muted);">Leave empty for no expiry</small>
                    </div>

                    <div class="setting-toggle-row" style="border-bottom: none;">
                        <span class="setting-text">
                            <span class="label">Pin to Dashboard</span>
                            <span class="help">Pinned notices will show on dashboard</span>
                        </span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="pin_to_dashboard" value="1" @checked(old('pin_to_dashboard'))>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-users"></i> Audience</div>
                    <div class="form-group">
                        <label class="form-label">Send To <span class="required">*</span></label>
                        @php
                            $audiences = [
                                'all_members' => 'All Members',
                                'selected_members' => 'Selected Members',
                                'selected_units' => 'Selected Units / Flats',
                                'selected_towers' => 'Selected Towers / Buildings',
                                'custom' => 'Custom (Email / Mobile)',
                            ];
                        @endphp
                        @foreach($audiences as $value => $label)
                            <label style="display: flex; align-items: center; gap: 8px; padding: 8px 0; cursor: pointer;">
                                <input type="radio" name="audience_type" value="{{ $value }}" @checked(old('audience_type', 'all_members') === $value) required>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="estimated_recipients" value="245">
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>Notice will be sent to all registered members across all societies.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-body" style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('superadmin.notices.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="status" value="draft" class="btn btn-outline-secondary"><i class="fas fa-eye"></i> Preview Notice</button>
            <button type="submit" name="status" value="published" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publish Notice</button>
        </div>
    </div>
</form>
@endsection
