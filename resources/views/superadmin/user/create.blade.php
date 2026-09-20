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

<form action="{{ route('superadmin.users.store') }}" method="POST" class="user-form" id="userCreateForm">
    @csrf
    <div class="user-form-layout">
        <div class="card">
            <div class="card-body">
                <div class="form-section">
                    <div class="form-section-head">
                        <div class="form-section-icon"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="form-section-title">Personal Details</div>
                            <div class="form-section-desc">Who this account belongs to and how to reach them.</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Rahul Sharma" value="{{ old('name') }}" autocomplete="name" autofocus required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" value="{{ old('email') }}" autocomplete="email" required>
                            </div>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">The password setup link will be sent here.</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="mobile">Mobile <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror" placeholder="10-digit mobile number" value="{{ old('mobile') }}" autocomplete="tel-national" inputmode="numeric" required>
                            </div>
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="alternate_mobile">Alternate Mobile <span class="optional">(optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" id="alternate_mobile" name="alternate_mobile" class="form-control @error('alternate_mobile') is-invalid @enderror" placeholder="Backup contact number" value="{{ old('alternate_mobile') }}" inputmode="numeric">
                            </div>
                            @error('alternate_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-head">
                        <div class="form-section-icon"><i class="fas fa-key"></i></div>
                        <div>
                            <div class="form-section-title">Role &amp; Access</div>
                            <div class="form-section-desc">Controls which panel the user can sign in to and what they can see.</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="role_id">Role <span class="required">*</span></label>
                            <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                <option value="">Select a role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-role-name="{{ $role->name }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="society_id">Society</label>
                            <select id="society_id" name="society_id" class="form-control @error('society_id') is-invalid @enderror">
                                <option value="">Platform-wide (Super Admin only)</option>
                                @foreach($societies as $society)
                                    <option value="{{ $society->id }}" {{ old('society_id', request('society_id')) == $society->id ? 'selected' : '' }}>{{ $society->name }}</option>
                                @endforeach
                            </select>
                            @error('society_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text" id="societyHint">Required for every society-level role.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <div class="status-toggle">
                            <input type="radio" id="status_active" name="status" value="active" {{ old('status', 'active') === 'active' ? 'checked' : '' }} required>
                            <label for="status_active">
                                <span class="status-dot active"></span>
                                <span>
                                    <span class="status-name">Active</span>
                                    <span class="status-hint" style="display:block;">Can sign in as soon as the password is set</span>
                                </span>
                            </label>

                            <input type="radio" id="status_inactive" name="status" value="inactive" {{ old('status') === 'inactive' ? 'checked' : '' }}>
                            <label for="status_inactive">
                                <span class="status-dot inactive"></span>
                                <span>
                                    <span class="status-name">Inactive</span>
                                    <span class="status-hint" style="display:block;">Account is created but sign-in is blocked</span>
                                </span>
                            </label>
                        </div>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-section">
                    <div class="setup-notice">
                        <i class="fas fa-envelope-open-text"></i>
                        <div>
                            <strong>Account setup</strong>
                            <p>No password is needed now. A secure, 60-minute password setup link will be emailed to the user right after the account is created.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save User</button>
            </div>
        </div>

        <aside class="user-form-aside">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-shield-alt" style="color: var(--primary);"></i> Permission Summary</div></div>
                <div class="card-body">
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; line-height: 1.5;">Permissions are assigned automatically based on the selected role.</p>
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
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        var role = document.getElementById('role_id');
        var society = document.getElementById('society_id');
        var hint = document.getElementById('societyHint');
        if (!role || !society) return;

        function sync() {
            var opt = role.options[role.selectedIndex];
            var isSuperAdmin = opt && opt.dataset.roleName === 'super_admin';
            society.disabled = isSuperAdmin;
            if (isSuperAdmin) {
                society.value = '';
                hint.textContent = 'Super Admins are platform-wide and are not tied to a society.';
            } else {
                hint.textContent = 'Required for every society-level role.';
            }
        }

        role.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
