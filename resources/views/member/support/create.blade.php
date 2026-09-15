@extends('member.layouts.app')

@section('title', 'Raise a Request')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Raise a Complaint / Request</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.support.index') }}">Complaints & Requests</a>
                <span class="breadcrumb-separator">/</span>
                <span>New</span>
            </div>
        </div>
        <a href="{{ route('member.support.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<form method="POST" action="{{ route('member.support.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="content-grid" style="grid-template-columns: 2fr 1fr;">
        <div class="card">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
                @endif
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Priority <span class="required">*</span></label>
                        <select name="priority" class="form-control" required>
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject <span class="required">*</span></label>
                    <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" maxlength="255" required placeholder="e.g. Water leakage in bathroom">
                </div>
                <div class="form-group">
                    <label class="form-label">Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control" rows="5" maxlength="1000" required placeholder="Describe the issue in detail">{{ old('description') }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Kitchen, Parking B-12">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred contact</label>
                        <select name="preferred_contact" class="form-control">
                            <option value="">No preference</option>
                            @foreach($contactMethods as $method)
                                <option value="{{ $method }}" {{ old('preferred_contact') === $method ? 'selected' : '' }}>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-text">JPG, PNG or PDF up to 5 MB.</div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <a href="{{ route('member.support.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Raised by</div>
                    <div class="review-row"><span class="review-label">Name</span><span class="review-value">{{ $member->name }}</span></div>
                    <div class="review-row"><span class="review-label">Flat</span><span class="review-value">{{ $member->flat_unit ?: '—' }}{{ $member->tower_wing ? ' · '.$member->tower_wing : '' }}</span></div>
                    <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $member->mobile ?: '—' }}</span></div>
                    <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $member->email ?: '—' }}</span></div>
                </div>
            </div>
            <div class="info-box" style="margin-top: 16px;">
                <i class="fas fa-circle-info"></i>
                <span>The society office is notified immediately and will reply on this request. You will get an email when they do.</span>
            </div>
        </div>
    </div>
</form>
@endsection
