@extends('society.layouts.app')

@section('title', 'Add New AMC')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New AMC</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amc.index') }}">AMC &amp; Renewal Tracker</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New AMC</span>
            </div>
        </div>
        <a href="{{ route('society.amc.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to AMC List</a>
    </div>
</div>

<form method="POST" action="{{ route('society.amc.store') }}">
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
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">AMC Information</div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Item / Asset <span class="required">*</span></label>
                    <select name="item_asset" class="form-control" required>
                        <option value="">Select item / asset</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->name }}" {{ old('item_asset') === $asset->name ? 'selected' : '' }}>{{ $asset->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Select the asset or system for AMC.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select name="amc_category_id" class="form-control" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('amc_category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Select the AMC category.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Vendor / Service Provider <span class="required">*</span></label>
                    <select name="service_vendor_id" class="form-control" required>
                        <option value="">Select vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ (string) old('service_vendor_id') === (string) $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    <div style="text-align: right; margin-top: 8px;">
                        <a href="{{ route('society.vendors.create') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;"><i class="fas fa-plus"></i> Add New Vendor</a>
                    </div>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Contract No.</label>
                    <input type="text" name="contract_no" value="{{ old('contract_no') }}" class="form-control" placeholder="Enter contract number">
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Enter AMC contract or reference number.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">PO / Invoice No.</label>
                    <input type="text" name="po_invoice_no" value="{{ old('po_invoice_no') }}" class="form-control" placeholder="Enter PO / invoice number (optional)">
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Enter related PO or invoice number.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Contract Type <span class="required">*</span></label>
                    <select name="contract_type" class="form-control" required>
                        @foreach($contractTypes as $type)
                            <option value="{{ $type }}" {{ old('contract_type', 'Comprehensive') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Select the type of AMC contract.</div>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Start Date <span class="required">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control" required>
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Contract start date.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date / Expiry Date <span class="required">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control" required>
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Contract expiry date.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <input type="number" name="duration_months" value="{{ old('duration_months', 12) }}" min="1" class="form-control" placeholder="12">
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Total duration of the contract (months).</div>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="Enter amount" required>
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Total AMC amount (inclusive of tax if any).</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tax (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_percent" value="{{ old('tax_percent') }}" class="form-control" placeholder="Enter tax percentage">
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Enter applicable tax percentage.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Renewal Reminder</label>
                    <input type="number" name="renewal_reminder_days" value="{{ old('renewal_reminder_days', 30) }}" min="0" class="form-control" placeholder="30">
                    <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Reminder before expiry (in days).</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="4" maxlength="500" placeholder="Enter description (scope of work, terms, etc.)">{{ old('description') }}</textarea>
                <div class="form-help" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Add scope of work, terms &amp; conditions or any other details.</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="section-title" style="font-size: 16px;">Attachments (Optional)</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Upload related files (contract copy, quotation, invoice, etc.)</div>
            <div style="border: 2px dashed var(--border); border-radius: 10px; padding: 32px; text-align: center; color: var(--text-muted);">
                <i class="fas fa-cloud-arrow-up" style="font-size: 28px; margin-bottom: 10px;"></i>
                <div>Drag and drop files here or <span style="color: var(--primary);">click to browse</span></div>
                <div style="font-size: 11px; margin-top: 6px;">Supports: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max. 10MB each)</div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <a href="{{ route('society.amc.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="action" value="draft" class="btn btn-secondary">Save as Draft</button>
        <button type="submit" name="action" value="save" class="btn btn-primary">Save AMC</button>
    </div>
</form>
@endsection
