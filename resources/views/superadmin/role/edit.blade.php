@extends('superadmin.layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Role: {{ $role->display_name }}</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.roles.index') }}">Role Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Edit Role</span>
    </div>
</div>

<form action="{{ route('superadmin.roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <div class="card-title">Role Details</div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Role Name (slug) <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="e.g. society_admin" value="{{ old('name', $role->name) }}" required>
                    @error('name')<div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Display Name <span class="required">*</span></label>
                    <input type="text" name="display_name" class="form-control {{ $errors->has('display_name') ? 'is-invalid' : '' }}" placeholder="e.g. Society Admin" value="{{ old('display_name', $role->display_name) }}" required>
                    @error('display_name')<div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter role description...">{{ old('description', $role->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', $role->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $role->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if($permissions->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <div class="card-title">Assign Permissions</div>
        </div>
        <div class="card-body">
            @foreach($permissions as $module => $modulePermissions)
            <div style="margin-bottom: 20px;">
                <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--border-color);">
                    {{ ucfirst($module ?? 'General') }}
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px;">
                    @foreach($modulePermissions as $permission)
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, old('permissions', $assignedPermissions)) ? 'checked' : '' }}>
                        <span>{{ $permission->display_name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-bottom: 40px;">
        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Role</button>
    </div>
</form>
@endsection
