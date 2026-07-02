@extends('society.layouts.app')

@section('title', 'Request '.$request->request_id)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $request->request_id }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.support.index') }}">Priority Support</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $request->request_id }}</span>
            </div>
        </div>
        <a href="{{ route('society.support.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Requests</a>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 0;">{{ $request->subject }}</div>
                    <span class="status-badge {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span>
                </div>

                <div class="review-row"><span class="review-label">Category</span><span class="review-value"><span class="badge {{ $request->categoryBadgeClass() }}">{{ $request->category }}</span></span></div>
                <div class="review-row"><span class="review-label">Priority</span><span class="review-value"><span class="badge {{ $request->priorityBadgeClass() }}">{{ $request->priorityLabel() }}</span></span></div>
                <div class="review-row"><span class="review-label">Raised By</span><span class="review-value">{{ $request->raised_by_name }} ({{ $request->raised_by_type === 'member' ? 'Member' : 'Staff / Admin' }})</span></div>
                <div class="review-row"><span class="review-label">Unit / Flat</span><span class="review-value">{{ $request->flat_no ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $request->mobile ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $request->email ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Location</span><span class="review-value">{{ $request->location ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Raised On</span><span class="review-value">{{ $request->raised_at?->format('d M Y, h:i A') }}</span></div>

                <div style="margin-top: 20px;">
                    <div class="form-label">Problem Description</div>
                    <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">{{ $request->description }}</p>
                </div>

                @if($request->notes)
                    <div style="margin-top: 16px;">
                        <div class="form-label">Additional Notes</div>
                        <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">{{ $request->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Our support team will respond to your request as soon as possible.</span>
        </div>
    </div>
</div>
@endsection
