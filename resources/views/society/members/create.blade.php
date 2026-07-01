@extends('society.layouts.app')

@section('title', 'Add Member')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Member</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.members.index') }}">Member Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Member</span>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
@endif

<form method="POST" action="{{ route('society.members.store') }}">
    @csrf
    <div class="card">
        <div class="card-header"><div class="card-title">Member Details</div></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Member Type <span class="required">*</span></label>
                    <select name="member_type" class="form-control" required>
                        <option value="owner" {{ old('member_type') === 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="family_member" {{ old('member_type') === 'family_member' ? 'selected' : '' }}>Family Member</option>
                        <option value="tenant" {{ old('member_type') === 'tenant' ? 'selected' : '' }}>Tenant</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Flat / Unit</label>
                    <input type="text" name="flat_unit" class="form-control" value="{{ old('flat_unit') }}" placeholder="A-101">
                </div>
                <div class="form-group">
                    <label class="form-label">Tower / Wing</label>
                    <input type="text" name="tower_wing" class="form-control" value="{{ old('tower_wing') }}" placeholder="Tower A">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" placeholder="9876543210">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="name@email.com">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Join Date</label>
                    <input type="date" name="join_date" class="form-control" value="{{ old('join_date', now()->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-bottom: 24px;">
        <a href="{{ route('society.members.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Member</button>
    </div>
</form>
@endsection
