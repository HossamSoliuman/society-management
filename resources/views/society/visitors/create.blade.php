@extends('society.layouts.app')

@section('title', 'Check In Visitor')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Check In Visitor</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.visitors.index') }}">Visitors</a>
                <span class="breadcrumb-separator">/</span>
                <span>Check In</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.visitors.store') }}">
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
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Visitor Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Visiting Unit <span class="required">*</span></label>
                    <select name="unit_id" class="form-control" required>
                        <option value="">Select unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>{{ $unit->unit_number }}{{ $unit->occupied_by_name ? ' · '.$unit->occupied_by_name : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Purpose <span class="required">*</span></label>
                    <select name="purpose" class="form-control" required>
                        @foreach($purposes as $value => $meta)
                            <option value="{{ $value }}" @selected(old('purpose', 'guest') === $value)>{{ $meta[0] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control" value="{{ old('company') }}" placeholder="e.g. Amazon, Urban Company">
                </div>
                <div class="form-group">
                    <label class="form-label">People <span class="required">*</span></label>
                    <input type="number" name="people_count" class="form-control" min="1" max="100" value="{{ old('people_count', 1) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Vehicle Number</label>
                    <input type="text" name="vehicle_no" class="form-control" value="{{ old('vehicle_no') }}" style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label class="form-label">Note</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" maxlength="255">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('society.visitors.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-right-to-bracket"></i> Check In</button>
            </div>
        </div>
    </div>
</form>
@endsection
