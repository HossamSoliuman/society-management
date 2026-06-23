@extends('superadmin.layouts.app')

@section('title', 'Add Terms & Conditions')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div><h1 class="page-title">Add Terms & Conditions</h1></div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.terms.index') }}">Terms & Conditions</a>
        <span class="breadcrumb-separator">/</span>
        <span>Add New</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('superadmin.terms.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Document Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Enter document title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Document Type <span class="required">*</span></label>
                    <select name="document_type" class="form-control" required>
                        <option value="member_app">Member App</option>
                        <option value="web_portal">Web Portal</option>
                        <option value="other_documents">Other Documents</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Applies To <span class="required">*</span></label>
                    <input type="text" name="applies_to" class="form-control" placeholder="e.g. All Societies" value="All Societies" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Version <span class="required">*</span></label>
                    <input type="text" name="version" class="form-control" placeholder="e.g. 1.0" value="1.0" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Content <span class="required">*</span></label>
                <textarea name="content" class="form-control" rows="10" placeholder="Enter terms and conditions content..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-control">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('superadmin.terms.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Document</button>
            </div>
        </form>
    </div>
</div>
@endsection
