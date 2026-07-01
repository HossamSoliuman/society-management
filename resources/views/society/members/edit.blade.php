@extends('society.layouts.app')

@section('title', 'Edit Member')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Member</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.members.index') }}">Member Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit Member</span>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
@endif

<form method="POST" action="{{ route('society.members.update', $member) }}">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header"><div class="card-title">Member Details</div></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Member Type <span class="required">*</span></label>
                    <select name="member_type" class="form-control" required>
                        <option value="owner" {{ old('member_type', $member->member_type) === 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="family_member" {{ old('member_type', $member->member_type) === 'family_member' ? 'selected' : '' }}>Family Member</option>
                        <option value="tenant" {{ old('member_type', $member->member_type) === 'tenant' ? 'selected' : '' }}>Tenant</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Flat / Unit</label>
                    <input type="text" name="flat_unit" class="form-control" value="{{ old('flat_unit', $member->flat_unit) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tower / Wing</label>
                    <input type="text" name="tower_wing" class="form-control" value="{{ old('tower_wing', $member->tower_wing) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $member->mobile) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $member->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ old('status', $member->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Join Date</label>
                    <input type="date" name="join_date" class="form-control" value="{{ old('join_date', optional($member->join_date)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-bottom: 24px;">
        <a href="{{ route('society.members.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Changes</button>
    </div>
</form>
@endsection
