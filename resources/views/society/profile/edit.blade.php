@extends('society.layouts.app')

@section('title', 'Edit Society Profile')

@php
    $allAmenities = ['Club House', 'Gymnasium', 'Children Play Area', 'Swimming Pool', 'Garden', '24x7 Security', 'Power Backup', 'Lift / Elevators', 'Indoor Games', 'Jogging Track', 'Visitor Parking', 'CCTV Surveillance'];
    $selectedAmenities = $society->amenities ?? [];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Society Profile</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.profile') }}">Society Profile</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit</span>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

<form method="POST" action="{{ route('society.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header"><div class="card-title">Basic Information</div></div>
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Society Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $society->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Society Code</label>
                    <input type="text" name="society_code" class="form-control" value="{{ old('society_code', $society->society_code) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $society->registration_number) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">RERA Number</label>
                    <input type="text" name="rera_number" class="form-control" value="{{ old('rera_number', $society->rera_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Building Type</label>
                    <input type="text" name="building_type" class="form-control" value="{{ old('building_type', $society->building_type) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Year of Establishment</label>
                    <input type="number" name="year_established" class="form-control" value="{{ old('year_established', $society->year_established) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Wings</label>
                    <input type="number" name="wings_count" class="form-control" value="{{ old('wings_count', $society->wings_count) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Blocks</label>
                    <input type="number" name="blocks_count" class="form-control" value="{{ old('blocks_count', $society->blocks_count) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Total Units</label>
                    <input type="number" name="total_units" class="form-control" value="{{ old('total_units', $society->total_units) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Society Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <div class="form-text">Square image recommended. Max 2MB.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Building Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    <div class="form-text">Max 4MB.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Contact Information</div></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line_1" class="form-control" value="{{ old('address_line_1', $society->address_line_1) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line_2" class="form-control" value="{{ old('address_line_2', $society->address_line_2) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $society->city) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state', $society->state) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $society->pincode) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="primary_mobile" class="form-control" value="{{ old('primary_mobile', $society->primary_mobile) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="primary_email" class="form-control" value="{{ old('primary_email', $society->primary_email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" class="form-control" value="{{ old('website', $society->website) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Office Timings</label>
                <input type="text" name="office_timings" class="form-control" value="{{ old('office_timings', $society->office_timings) }}">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Administrative &amp; Financial Details</div></div>
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Management Type</label>
                    <input type="text" name="management_type" class="form-control" value="{{ old('management_type', $society->management_type) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Managing Committee (Members)</label>
                    <input type="number" name="committee_members_count" class="form-control" value="{{ old('committee_members_count', $society->committee_members_count) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Audit Type</label>
                    <input type="text" name="audit_type" class="form-control" value="{{ old('audit_type', $society->audit_type) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Financial Year</label>
                    <input type="text" name="financial_year" class="form-control" value="{{ old('financial_year', $society->financial_year) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Maintenance Collection Day</label>
                    <input type="text" name="maintenance_collection_day" class="form-control" value="{{ old('maintenance_collection_day', $society->maintenance_collection_day) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $society->bank_name) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $society->account_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code', $society->ifsc_code) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $society->pan_number) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">GST Number</label>
                <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $society->gst_number) }}">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Amenities</div></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                @foreach($allAmenities as $amenity)
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" name="amenities[]" value="{{ $amenity }}"
                            {{ in_array($amenity, old('amenities', $selectedAmenities)) ? 'checked' : '' }}>
                        <span class="form-check-label">{{ $amenity }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">About Society</div></div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">About</label>
                <textarea name="about" class="form-control" rows="5">{{ old('about', $society->about) }}</textarea>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-bottom: 24px;">
        <a href="{{ route('society.profile') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Changes</button>
    </div>
</form>
@endsection
