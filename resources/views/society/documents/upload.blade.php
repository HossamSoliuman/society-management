@extends('society.layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Upload Document</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.documents.index') }}">Document Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Upload Document</span>
            </div>
        </div>
        <a href="{{ route('society.documents.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Documents</a>
    </div>
</div>

<form method="POST" action="{{ route('society.documents.store') }}">
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
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Document Details</div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label">Document Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter document name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select name="document_category_id" class="form-control" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('document_category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label">Document Type <span class="required">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="">Select document type</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" maxlength="250" placeholder="Enter description (optional)">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label">Related To</label>
                    <select name="related_to" class="form-control">
                        <option value="">Select (Optional)</option>
                        @foreach($relatedOptions as $option)
                            <option value="{{ $option }}" {{ old('related_to') === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" class="form-control" placeholder="Enter tags separated by commas">
                    <span style="font-size: 11px; color: var(--text-muted);">Helps in quick search and filtering</span>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label">Expiry Date (Optional)</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Confidentiality</label>
                    <select name="confidentiality" class="form-control">
                        @foreach($confidentialityLevels as $value => $label)
                            <option value="{{ $value }}" {{ old('confidentiality', 'general') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span style="font-size: 11px; color: var(--text-muted);">Choose who can access this document</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Upload File <span class="required">*</span></label>
                <div style="border: 2px dashed var(--border-color); border-radius: 10px; padding: 36px; text-align: center;">
                    <div style="font-size: 32px; color: var(--text-muted); margin-bottom: 10px;"><i class="fas fa-cloud-arrow-up"></i></div>
                    <div style="font-weight: 600;">Drag and drop your file here, or <span style="color: var(--primary);">click to browse</span></div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">Supports: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max. 20MB)</div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                <a href="{{ route('society.documents.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Document</button>
            </div>
        </div>
    </div>
</form>
@endsection
