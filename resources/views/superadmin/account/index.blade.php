@extends('superadmin.layouts.app')

@section('title', 'My Account')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Account</h1>
            <p class="page-subtitle">Manage your profile and security settings.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>My Account</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user" style="color: var(--primary);"></i> Profile Information</div>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.account.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $user->mobile ?? '') }}">
                    @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-lock" style="color: var(--primary);"></i> Change Password</div>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.account.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Current Password <span class="required">*</span></label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">New Password <span class="required">*</span></label>
                    <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required>
                    @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password <span class="required">*</span></label>
                    <input type="password" name="new_password_confirmation" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
                </div>
            </form>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-id-card" style="color: var(--primary);"></i> Account Summary</div>
    </div>
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="avatar" style="width: 72px; height: 72px; border-radius: 50%; overflow: hidden; flex-shrink: 0;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=2563EB&color=fff&size=72" alt="">
            </div>
            <div>
                <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">{{ $user->name }}</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;"><i class="fas fa-envelope" style="margin-right: 6px;"></i>{{ $user->email }}</div>
                @if($user->mobile ?? false)
                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;"><i class="fas fa-phone" style="margin-right: 6px;"></i>{{ $user->mobile }}</div>
                @endif
                <div style="margin-top: 8px;">
                    <span class="status-badge active">Super Admin</span>
                    @if($user->created_at)
                    <span style="font-size: 12px; color: var(--text-muted); margin-left: 10px;">Member since {{ $user->created_at->format('M Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
