@php
    /**
     * @var \App\Models\User|null $user
     * @var string $action
     * @var string $mode           'create' | 'edit'
     * @var \Illuminate\Support\Collection $roles
     */
    $isEdit = ($mode ?? 'create') === 'edit';
    $currentRoleId = old('role_id', $user?->roles->first()?->id);
    $statusValue = old('status', $user?->status ?? 'active');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="content-grid">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-user"></i></div>
                        <div><div class="title" style="font-size: 16px;">User Details</div></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user?->name) }}" class="form-control" placeholder="Enter full name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user?->email) }}" class="form-control" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" value="{{ old('mobile', $user?->mobile) }}" class="form-control" placeholder="Enter mobile number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role <span class="required">*</span></label>
                            <select name="role_id" class="form-control" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ (string) $currentRoleId === (string) $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control">
                            <option value="active" {{ $statusValue === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $statusValue === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('society.settings.users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $isEdit ? 'Save Changes' : 'Invite User' }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">What each role can do</div>
                    @foreach($roles as $role)
                        <div style="margin-bottom: 12px;">
                            <div style="font-weight: 600; font-size: 13px;">{{ $role->display_name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $role->permissions->pluck('display_name')->implode(', ') ?: 'No module access' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span>{{ $isEdit ? 'Changing the role takes effect on the next page load.' : 'An email with a secure password setup link is sent to active users.' }}</span>
            </div>
        </div>
    </div>
</form>
