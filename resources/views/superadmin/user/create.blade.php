@extends('superadmin.layouts.app')

@section('title', 'Add New Society User')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Society User</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.users.index') }}">User Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Add New User</span>
    </div>
</div>

<form action="{{ route('superadmin.users.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-user"></i> User Information</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select name="role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope" style="font-size: 12px;"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="Enter email address" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="font-size: 11px;">+91</span>
                            <input type="text" name="mobile" class="form-control" placeholder="Enter mobile number" value="{{ old('mobile') }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alternate Mobile</label>
                        <div class="input-group">
                            <span class="input-group-text" style="font-size: 11px;">+91</span>
                            <input type="text" name="alternate_mobile" class="form-control" placeholder="Enter alternate mobile" value="{{ old('alternate_mobile') }}">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Society</label>
                        <select name="society_id" class="form-control">
                            <option value="">Platform-wide (Super Admin only)</option>
                            @foreach($societies as $society)
                                <option value="{{ $society->id }}" {{ old('society_id', request('society_id')) == $society->id ? 'selected' : '' }}>{{ $society->name }}</option>
                            @endforeach
                        </select>
                        <small style="color:var(--text-muted);">Required for every society-level role.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Setup</label>
                        <div style="padding:12px 14px; border-radius:8px; background:#fff7ed; color:#9a3412; font-size:12px; line-height:1.5;">
                            <i class="fas fa-envelope-open-text"></i> A secure, 60-minute password setup link will be emailed after creation.
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-shield-alt" style="color: var(--primary);"></i> Permission Summary</div></div>
                <div class="card-body">
                    <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">Permissions will be automatically assigned based on the selected role.</p>
                    <ul class="info-list">
                        <li><i class="fas fa-check"></i> Role controls the accessible application area</li>
                        <li><i class="fas fa-check"></i> Society users are isolated to the selected society</li>
                        <li><i class="fas fa-check"></i> Super Admin is the only platform-wide role</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-info-circle" style="color: var(--primary);"></i> Important Information</div></div>
                <div class="card-body">
                    <ul class="info-list">
                        <li><i class="fas fa-check"></i> Passwords are never sent by email</li>
                        <li><i class="fas fa-check"></i> Setup links expire after 60 minutes</li>
                        <li><i class="fas fa-check"></i> Links can be resent from User Management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-bottom: 40px;">
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save User</button>
    </div>
</form>
@endsection
