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

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock" style="font-size: 12px;"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock" style="font-size: 12px;"></i></span>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Designation (Optional)</label>
                        <input type="text" name="designation" class="form-control" placeholder="Enter designation" value="{{ old('designation') }}">
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
                        <li><i class="fas fa-check"></i> Role: Super Admin (Full Access)</li>
                        <li><i class="fas fa-check"></i> Society: All Societies</li>
                        <li><i class="fas fa-check"></i> Modules: All Modules</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-info-circle" style="color: var(--primary);"></i> Important Information</div></div>
                <div class="card-body">
                    <ul class="info-list">
                        <li><i class="fas fa-check"></i> User will receive an email with login credentials</li>
                        <li><i class="fas fa-check"></i> Password must be at least 8 characters</li>
                        <li><i class="fas fa-check"></i> User can change their password after first login</li>
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
