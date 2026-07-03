@extends('society.layouts.app')

@section('title', 'Create New Tender')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create New Tender</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenders.active') }}">Tender Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Create New Tender</span>
            </div>
        </div>
        <a href="{{ route('society.tenders.active') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Tenders</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<form method="POST" action="{{ route('society.tenders.store') }}">
    @csrf
    <div class="content-grid">
        <div>
            {{-- 1. Tender Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title">1. Tender Information</div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Tender Title <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Enter tender title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="Auto-generated if blank">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tender Type <span style="color: var(--danger);">*</span></label>
                            <select name="tender_type" class="form-control" required>
                                @foreach($tenderTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('tender_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department <span style="color: var(--danger);">*</span></label>
                            <select name="department" class="form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Description <span style="color: var(--danger);">*</span></label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter tender description" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tender Category <span style="color: var(--danger);">*</span></label>
                            <select name="tender_category" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('tender_category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estimated Value (&#8377;)</label>
                            <input type="number" step="0.01" name="estimated_value" value="{{ old('estimated_value') }}" class="form-control" placeholder="Enter estimated value">
                        </div>
                        <div class="form-group">
                            <label class="form-label">EMD Amount (&#8377;)</label>
                            <input type="number" step="0.01" name="emd_amount" value="{{ old('emd_amount') }}" class="form-control" placeholder="Enter EMD amount">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Validity (Days)</label>
                            <input type="number" name="validity_days" value="{{ old('validity_days') }}" class="form-control" placeholder="Enter validity in days">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bid Submission Deadline</label>
                            <input type="datetime-local" name="submission_deadline" value="{{ old('submission_deadline') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tender Opening Date</label>
                            <input type="datetime-local" name="opening_date" value="{{ old('opening_date') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Terms & Conditions --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title">2. Terms &amp; Conditions</div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Payment Terms</label>
                            <select name="payment_terms" class="form-control">
                                <option value="">Select Payment Terms</option>
                                @foreach($paymentTerms as $term)
                                    <option value="{{ $term }}" {{ old('payment_terms') === $term ? 'selected' : '' }}>{{ $term }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Delivery Terms</label>
                            <select name="delivery_terms" class="form-control">
                                <option value="">Select Delivery Terms</option>
                                @foreach($deliveryTerms as $term)
                                    <option value="{{ $term }}" {{ old('delivery_terms') === $term ? 'selected' : '' }}>{{ $term }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contract Type</label>
                            <select name="contract_type" class="form-control">
                                <option value="">Select Contract Type</option>
                                @foreach($contractTypes as $type)
                                    <option value="{{ $type }}" {{ old('contract_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tax Applicable</label>
                            <select name="tax_option" class="form-control">
                                <option value="">Select Tax Option</option>
                                @foreach($taxOptions as $option)
                                    <option value="{{ $option }}" {{ old('tax_option') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Terms &amp; Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="3" placeholder="Enter terms and conditions">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Additional Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title">3. Additional Information</div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Eligibility Criteria</label>
                            <input type="text" name="eligibility_criteria" value="{{ old('eligibility_criteria') }}" class="form-control" placeholder="Enter eligibility criteria">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Evaluation Criteria</label>
                            <input type="text" name="evaluation_criteria" value="{{ old('evaluation_criteria') }}" class="form-control" placeholder="Enter evaluation criteria">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Person <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control" placeholder="Enter contact person name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Email <span style="color: var(--danger);">*</span></label>
                            <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-control" placeholder="Enter email address" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Phone <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control" placeholder="Enter contact number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Venue (If any)</label>
                            <input type="text" name="venue" value="{{ old('venue') }}" class="form-control" placeholder="Enter venue">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tender Notice Visibility</label>
                            <select name="visibility" class="form-control">
                                <option value="">Select Visibility</option>
                                @foreach($visibilityOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('visibility') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: center; gap: 20px; padding-top: 24px;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px;">
                                <input type="checkbox" name="allow_online_submission" value="1" {{ old('allow_online_submission', true) ? 'checked' : '' }}> Allow Online Submission
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px;">
                                <input type="checkbox" name="allow_partial_bidding" value="1" {{ old('allow_partial_bidding') ? 'checked' : '' }}> Allow Partial Bidding
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Attachments --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title">4. Attachments</div>
                    <div style="border: 2px dashed var(--border); border-radius: var(--radius-md); padding: 32px; text-align: center; color: var(--text-muted);">
                        <i class="fas fa-cloud-arrow-up" style="font-size: 24px;"></i>
                        <div style="margin-top: 8px;">Drag &amp; drop files here or <span style="color: var(--primary);">Choose Files</span></div>
                        <div style="font-size: 11px;">PDF, DOC, DOCX, XLS, XLSX, ZIP up to 10MB</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Tender Timeline</div>
                    @foreach(['Tender Published' => 'Will be set after publishing', 'Bid Submission Deadline' => 'Not set', 'Tender Opening' => 'Not set', 'Award Decision' => 'Not set', 'Contract Start' => 'Not set'] as $step => $desc)
                        <div class="rail-list-item">
                            <span class="rli-ico" style="background: var(--primary-light); color: var(--primary);"><i class="fas fa-circle-dot"></i></span>
                            <span class="rli-main">
                                <span class="rli-title">{{ $step }}</span>
                                <span class="rli-sub">{{ $desc }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Required Documents</div>
                    @foreach(['Tender Notice / NIT', 'BOQ / Scope of Work', 'Terms & Conditions', 'Eligibility Criteria', 'Other Documents'] as $doc)
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; padding: 6px 0;">
                            <input type="checkbox" checked> {{ $doc }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Notes</div>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Enter notes (optional)">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
        <a href="{{ route('society.tenders.active') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="action" value="draft" class="btn btn-secondary"><i class="fas fa-floppy-disk"></i> Save as Draft</button>
        <button type="submit" name="action" value="publish" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Save &amp; Publish</button>
    </div>
</form>
@endsection
