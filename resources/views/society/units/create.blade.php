@extends('society.layouts.app')

@section('title', 'Add Unit')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Unit</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Unit Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Unit</span>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
@endif

<form method="POST" action="{{ route('society.units.store') }}">
    @csrf
    <div class="card">
        <div class="card-header"><div class="card-title">Unit Details</div></div>
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Unit Number <span class="required">*</span></label>
                    <input type="text" name="unit_number" class="form-control" value="{{ old('unit_number') }}" placeholder="A-101" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Building</label>
                    <input type="text" name="building" class="form-control" value="{{ old('building') }}" placeholder="Building A">
                </div>
                <div class="form-group">
                    <label class="form-label">Wing / Block</label>
                    <input type="text" name="wing" class="form-control" value="{{ old('wing') }}" placeholder="Wing A">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Floor</label>
                    <input type="text" name="floor" class="form-control" value="{{ old('floor') }}" placeholder="1st Floor">
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Type</label>
                    <input type="text" name="unit_type" class="form-control" value="{{ old('unit_type') }}" placeholder="2 BHK">
                </div>
                <div class="form-group">
                    <label class="form-label">Area (sq.ft.)</label>
                    <input type="number" name="area_sqft" class="form-control" value="{{ old('area_sqft') }}" placeholder="950">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="occupied" {{ old('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="vacant" {{ old('status', 'vacant') === 'vacant' ? 'selected' : '' }}>Vacant</option>
                        <option value="under_maintenance" {{ old('status') === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Occupied By (Name)</label>
                    <input type="text" name="occupied_by_name" class="form-control" value="{{ old('occupied_by_name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Occupied By (Role)</label>
                    <input type="text" name="occupied_by_role" class="form-control" value="{{ old('occupied_by_role') }}" placeholder="Owner / Tenant">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Owner Name</label>
                    <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Owner Mobile</label>
                    <input type="text" name="owner_mobile" class="form-control" value="{{ old('owner_mobile') }}">
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-bottom: 24px;">
        <a href="{{ route('society.units.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Unit</button>
    </div>
</form>
@endsection
