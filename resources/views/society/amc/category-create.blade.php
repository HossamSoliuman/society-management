@extends('society.layouts.app')

@section('title', 'Add New AMC Category')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New AMC Category</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amc.index') }}">AMC &amp; Renewal Tracker</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amc.categories') }}">AMC Categories</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New Category</span>
            </div>
        </div>
        <a href="{{ route('society.amc.categories') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Categories</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;"><i class="fas fa-circle-exclamation"></i> <span>{{ $errors->first() }}</span></div>
@endif

<form method="POST" action="{{ route('society.amc.categories.store') }}">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="section-title">Category Information</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Category Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter category name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category Icon <span style="color: var(--danger);">*</span></label>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            @foreach($icons as $i => $icon)
                                <label class="icon-choice" style="cursor: pointer;">
                                    <input type="radio" name="icon" value="{{ $icon }}" {{ old('icon', $icons[0]) === $icon ? 'checked' : '' }} style="display: none;">
                                    <span class="rli-ico" style="background: var(--bg-muted); color: var(--text-secondary); width: 42px; height: 42px;"><i class="fas {{ $icon }}"></i></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter category description (optional)" maxlength="250">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span style="color: var(--danger);">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="section-title">Category Settings</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                <div class="form-group">
                    <label class="form-label">Applicable Assets</label>
                    <select name="applicable_assets[]" class="form-control" multiple size="4">
                        @foreach($applicableAssets as $asset)
                            <option value="{{ $asset }}" {{ in_array($asset, old('applicable_assets', [])) ? 'selected' : '' }}>{{ $asset }}</option>
                        @endforeach
                    </select>
                    <span style="font-size: 11px; color: var(--text-muted);">Select the types of assets/services under this category.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Reminder (Days Before Expiry)</label>
                    <input type="number" name="default_reminder_days" value="{{ old('default_reminder_days', 30) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Default AMC Duration (Months)</label>
                    <input type="number" name="default_duration_months" value="{{ old('default_duration_months', 12) }}" class="form-control">
                </div>
                <div class="form-group" style="padding-top: 28px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px;">
                        <input type="checkbox" name="tax_applicable" value="1" {{ old('tax_applicable') ? 'checked' : '' }}> Yes, tax is applicable for this category
                    </label>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Enter any additional notes..." maxlength="250">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <a href="{{ route('society.amc.categories') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Category</button>
    </div>
</form>
@endsection
