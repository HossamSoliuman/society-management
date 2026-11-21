@extends('society.layouts.app')

@section('title', 'Log Complaint')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Log Complaint</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.complaints.index') }}">Complaints</a>
                <span class="breadcrumb-separator">/</span>
                <span>Log Complaint</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.complaints.store') }}" enctype="multipart/form-data">
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
                    <label class="form-label">Resident <span class="required">*</span></label>
                    <select name="member_id" class="form-control" required>
                        <option value="">Select resident</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->flat_unit ? $member->flat_unit.' · ' : '' }}{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assign To</label>
                    <select name="assigned_to" class="form-control">
                        <option value="">Unassigned</option>
                        @foreach($team as $user)
                            <option value="{{ $user->id }}" @selected((string) old('assigned_to') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority <span class="required">*</span></label>
                    <select name="priority" class="form-control" required>
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Tower A lift lobby">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Subject <span class="required">*</span></label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" maxlength="255" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="6" required>{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Photo or document</label>
                <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('society.complaints.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Log Complaint</button>
            </div>
        </div>
    </div>
</form>
@endsection
