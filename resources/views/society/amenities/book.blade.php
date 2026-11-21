@extends('society.layouts.app')

@section('title', 'New Amenity Booking')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">New Booking</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amenities.bookings') }}">Bookings</a>
                <span class="breadcrumb-separator">/</span>
                <span>New</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.amenities.bookings.store') }}">
    @csrf

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
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Amenity <span class="required">*</span></label>
                    <select name="amenity_id" class="form-control" required>
                        <option value="">Select amenity</option>
                        @foreach($amenities as $amenity)
                            <option value="{{ $amenity->id }}" @selected((string) old('amenity_id', $selected) === (string) $amenity->id)>{{ $amenity->name }} · {{ $amenity->hoursLabel() }}{{ (float) $amenity->charge_per_hour > 0 ? ' · '.format_inr($amenity->charge_per_hour, 0).'/h' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Resident <span class="required">*</span></label>
                    <select name="member_id" class="form-control" required>
                        <option value="">Select resident</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->name }}{{ $member->flat_unit ? ' · '.$member->flat_unit : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Date <span class="required">*</span></label>
                    <input type="date" name="booking_date" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('booking_date', now()->addDay()->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">From <span class="required">*</span></label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '10:00') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">To <span class="required">*</span></label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '12:00') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Guests</label>
                    <input type="number" name="guests" class="form-control" min="0" value="{{ old('guests', 0) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}" placeholder="e.g. Birthday party">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('society.amenities.bookings') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Book</button>
            </div>
        </div>
    </div>
</form>
@endsection
