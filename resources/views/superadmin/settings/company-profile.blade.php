@extends('superadmin.layouts.app')

@section('title', 'Company Profile')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Company Profile</h1>
            <p class="page-subtitle">Manage your company profile information.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>System Settings</span>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.settings.company-profile') }}" class="sub-nav-tab active">Company Profile</a>
    <a href="{{ route('superadmin.settings.prefix') }}" class="sub-nav-tab">Prefix Settings</a>
    <a href="{{ route('superadmin.settings.smtp') }}" class="sub-nav-tab">SMTP Settings</a>
    <a href="{{ route('superadmin.settings.backup') }}" class="sub-nav-tab">Backup Settings</a>
    <a href="{{ route('superadmin.settings.security') }}" class="sub-nav-tab">Security Settings</a>
</div>

<form action="{{ route('superadmin.settings.company-profile.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-building"></i> Company Information</div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $company->company_name ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control" value="{{ $company->registration_number ?? '' }}">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $company->email ?? '' }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $company->phone ?? '' }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="{{ $company->website ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3">{{ $company->address ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-file-alt"></i> Tax Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number" class="form-control" value="{{ $company->gst_number ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">PAN Number</label>
                        <input type="text" name="pan_number" class="form-control" value="{{ $company->pan_number ?? '' }}">
                    </div>
                </div>

                <div class="section-title" style="margin-top: 24px;"><i class="fas fa-image"></i> Branding</div>
                <div class="form-group">
                    <label class="form-label">Company Logo</label>
                    <div class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div class="file-upload-text">Drag & drop logo here or</div>
                        <button type="button" class="btn btn-outline-primary btn-sm">Browse Files</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding-bottom: 40px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
</form>
@endsection
