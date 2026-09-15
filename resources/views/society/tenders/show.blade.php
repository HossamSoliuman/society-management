@extends('society.layouts.app')

@section('title', 'Tender '.$tender->reference_no)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $tender->title }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenders.active') }}">Tenders</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $tender->reference_no }}</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 8px;">
            @if($tender->status !== 'closed')
                <a href="{{ route('society.tenders.edit', $tender) }}" class="btn btn-secondary"><i class="fas fa-pencil"></i> Edit</a>
            @endif
            @if(in_array($tender->status, ['draft', 'under_review']))
                <form method="POST" action="{{ route('society.tenders.publish', $tender) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publish</button>
                </form>
            @endif
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 0;">Tender Details</div>
                    <span class="badge {{ $tender->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $tender->status)) }}</span>
                </div>
                <div class="review-row"><span class="review-label">Reference</span><span class="review-value">{{ $tender->reference_no }}</span></div>
                <div class="review-row"><span class="review-label">Type / Category</span><span class="review-value">{{ ucfirst($tender->tender_type) }} · {{ $tender->tender_category ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Department</span><span class="review-value">{{ $tender->department ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Estimated Value</span><span class="review-value">{{ $tender->estimated_value !== null ? format_inr($tender->estimated_value) : '—' }}</span></div>
                <div class="review-row"><span class="review-label">EMD</span><span class="review-value">{{ $tender->emd_amount !== null ? format_inr($tender->emd_amount) : '—' }}</span></div>
                <div class="review-row"><span class="review-label">Period</span><span class="review-value">{{ $tender->start_date?->format('d M Y') ?? '—' }} → {{ $tender->end_date?->format('d M Y') ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Submission Deadline</span><span class="review-value">{{ $tender->submission_deadline?->format('d M Y, h:i A') ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Opening</span><span class="review-value">{{ $tender->opening_date?->format('d M Y, h:i A') ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Terms</span><span class="review-value">{{ collect([$tender->payment_terms, $tender->delivery_terms, $tender->contract_type, $tender->tax_option])->filter()->implode(' · ') ?: '—' }}</span></div>
                <div class="review-row"><span class="review-label">Contact</span><span class="review-value">{{ collect([$tender->contact_person, $tender->contact_email, $tender->contact_phone])->filter()->implode(' · ') ?: '—' }}</span></div>
                @if($tender->awarded_vendor)
                    <div class="review-row"><span class="review-label">Awarded To</span><span class="review-value" style="font-weight: 700; color: var(--success);">{{ $tender->awarded_vendor }} · {{ format_inr($tender->contract_value) }} on {{ $tender->awarded_date?->format('d M Y') }}</span></div>
                @endif
                @if($tender->status === 'closed')
                    <div class="review-row"><span class="review-label">Closed</span><span class="review-value">{{ $tender->closed_date?->format('d M Y') }} — {{ $tender->closed_reason }}</span></div>
                @endif
                <div style="margin-top: 16px;">
                    <div class="form-label">Description</div>
                    <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">{{ $tender->description }}</p>
                </div>
                @if($tender->eligibility_criteria || $tender->evaluation_criteria)
                    <div style="margin-top: 12px;">
                        <div class="form-label">Eligibility / Evaluation</div>
                        <p style="color: var(--text-secondary); font-size: 13px;">{{ $tender->eligibility_criteria }}<br>{{ $tender->evaluation_criteria }}</p>
                    </div>
                @endif
                @if($tender->terms_conditions)
                    <div style="margin-top: 12px;">
                        <div class="form-label">Terms &amp; Conditions</div>
                        <p style="color: var(--text-secondary); font-size: 13px; white-space: pre-line;">{{ $tender->terms_conditions }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div>
        @if(in_array($tender->status, ['open', 'in_progress', 'under_review']))
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Award Tender</div>
                    <form method="POST" action="{{ route('society.tenders.award', $tender) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Vendor <span class="required">*</span></label>
                            <input type="text" name="awarded_vendor" class="form-control" list="vendorOptions" value="{{ old('awarded_vendor') }}" placeholder="Select or type vendor" required>
                            <datalist id="vendorOptions">
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contract Value (&#8377;) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="contract_value" class="form-control" value="{{ old('contract_value', $tender->estimated_value) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Award Date</label>
                            <input type="date" name="awarded_date" class="form-control" value="{{ old('awarded_date', now()->toDateString()) }}">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-trophy"></i> Award</button>
                    </form>
                </div>
            </div>
        @endif

        @if($tender->status !== 'closed')
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Close Tender</div>
                    <form method="POST" action="{{ route('society.tenders.close', $tender) }}" onsubmit="return confirm('Close this tender?');">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Reason</label>
                            <input type="text" name="closed_reason" class="form-control" placeholder="{{ $tender->status === 'awarded' ? 'Completed' : 'Cancelled' }}">
                        </div>
                        <button type="submit" class="btn btn-secondary" style="width: 100%;"><i class="fas fa-lock"></i> Close Tender</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Draft → Active (publish) → Awarded (select vendor) → Closed. Closed tenders are read-only.</span>
        </div>
    </div>
</div>
@endsection
