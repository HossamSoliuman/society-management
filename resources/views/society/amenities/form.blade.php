@extends('society.layouts.app')

@section('title', $amenity->exists ? 'Edit Amenity' : 'Add Amenity')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $amenity->exists ? $amenity->name : 'Add Amenity' }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amenities.index') }}">Amenities</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $amenity->exists ? 'Edit' : 'Add' }}</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ $amenity->exists ? route('society.amenities.update', $amenity) : route('society.amenities.store') }}">
    @csrf
    @if($amenity->exists)
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

    <div class="card">
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $amenity->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location', $amenity->location) }}" placeholder="e.g. Ground floor, Tower A">
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="1" value="{{ old('capacity', $amenity->capacity) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Opens <span class="required">*</span></label>
                    <input type="time" name="open_time" class="form-control" value="{{ old('open_time', substr((string) $amenity->open_time, 0, 5)) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Closes <span class="required">*</span></label>
                    <input type="time" name="close_time" class="form-control" value="{{ old('close_time', substr((string) $amenity->close_time, 0, 5)) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Hours per Booking <span class="required">*</span></label>
                    <input type="number" name="max_hours" class="form-control" min="1" max="24" value="{{ old('max_hours', $amenity->max_hours) }}" required>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Charge per Hour (₹)</label>
                    <input type="number" step="0.01" min="0" name="charge_per_hour" class="form-control" value="{{ old('charge_per_hour', $amenity->charge_per_hour) }}" placeholder="0 = free">
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" @selected(old('status', $amenity->status) === 'active')>Open for booking</option>
                        <option value="inactive" @selected(old('status', $amenity->status) === 'inactive')>Closed</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $amenity->requires_approval))> Office approves resident bookings
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Rules &amp; Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $amenity->description) }}</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('society.amenities.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save</button>
            </div>
        </div>
    </div>
</form>
@endsection
