@extends('society.layouts.app')

@section('title', 'Edit Unit')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Unit</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Unit Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit Unit</span>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
@endif

<form method="POST" action="{{ route('society.units.update', $unit) }}">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header"><div class="card-title">Unit Details</div></div>
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Unit Number <span class="required">*</span></label>
                    <input type="text" name="unit_number" class="form-control" value="{{ old('unit_number', $unit->unit_number) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Building</label>
                    <input type="text" name="building" class="form-control" value="{{ old('building', $unit->building) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Wing / Block</label>
                    <input type="text" name="wing" class="form-control" value="{{ old('wing', $unit->wing) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Floor</label>
                    <input type="text" name="floor" class="form-control" value="{{ old('floor', $unit->floor) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Type</label>
                    <input type="text" name="unit_type" class="form-control" value="{{ old('unit_type', $unit->unit_type) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Area (sq.ft.)</label>
                    <input type="number" name="area_sqft" class="form-control" value="{{ old('area_sqft', $unit->area_sqft) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="occupied" {{ old('status', $unit->status) === 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="vacant" {{ old('status', $unit->status) === 'vacant' ? 'selected' : '' }}>Vacant</option>
                        <option value="under_maintenance" {{ old('status', $unit->status) === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Occupied By (Name)</label>
                    <input type="text" name="occupied_by_name" class="form-control" value="{{ old('occupied_by_name', $unit->occupied_by_name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Occupied By (Role)</label>
                    <input type="text" name="occupied_by_role" class="form-control" value="{{ old('occupied_by_role', $unit->occupied_by_role) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Owner Name</label>
                    <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $unit->owner_name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Owner Mobile</label>
                    <input type="text" name="owner_mobile" class="form-control" value="{{ old('owner_mobile', $unit->owner_mobile) }}">
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-bottom: 24px;">
        <a href="{{ route('society.units.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Changes</button>
    </div>
</form>
@endsection
