@extends('superadmin.layouts.app')

@section('title', 'Edit Society')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Society</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.societies.index') }}">Society Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Edit</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('superadmin.societies.update', $society) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Society Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $society->name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control" value="{{ $society->registration_number }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prefix</label>
                    <input type="text" name="prefix" class="form-control" value="{{ $society->prefix }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Society Type</label>
                    <select name="society_type_id" class="form-control" required>
                        @foreach($societyTypes as $type)
                            <option value="{{ $type->id }}" {{ $society->society_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active" {{ $society->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $society->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                <a href="{{ route('superadmin.societies.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Society</button>
            </div>
        </form>
    </div>
</div>
@endsection
