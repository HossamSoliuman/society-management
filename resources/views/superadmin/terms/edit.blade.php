@extends('superadmin.layouts.app')

@section('title', 'Edit Terms & Conditions')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div><h1 class="page-title">Edit Terms & Conditions</h1></div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.terms.index') }}">Terms & Conditions</a>
        <span class="breadcrumb-separator">/</span>
        <span>Edit</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('superadmin.terms.update', $term) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Document Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ $term->title }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Document Type <span class="required">*</span></label>
                    <select name="document_type" class="form-control" required>
                        <option value="member_app" {{ $term->document_type == 'member_app' ? 'selected' : '' }}>Member App</option>
                        <option value="web_portal" {{ $term->document_type == 'web_portal' ? 'selected' : '' }}>Web Portal</option>
                        <option value="other_documents" {{ $term->document_type == 'other_documents' ? 'selected' : '' }}>Other Documents</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Applies To <span class="required">*</span></label>
                    <input type="text" name="applies_to" class="form-control" value="{{ $term->applies_to }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Version <span class="required">*</span></label>
                    <input type="text" name="version" class="form-control" value="{{ $term->version }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Content <span class="required">*</span></label>
                <textarea name="content" class="form-control" rows="10" required>{{ $term->content }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-control">
                    <option value="active" {{ $term->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $term->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('superadmin.terms.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Document</button>
            </div>
        </form>
    </div>
</div>
@endsection
