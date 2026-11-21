@extends('society.layouts.app')

@section('title', 'Request from '.$item->name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $item->name }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.member-requests.index') }}">Member Requests</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $item->name }}</span>
            </div>
        </div>
        <a href="{{ route('society.member-requests.index', ['status' => $item->status]) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

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

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 0;">{{ $item->typeLabel() }}</div>
                    <span class="badge {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>
                </div>
                <div class="review-row"><span class="review-label">Joining As</span><span class="review-value">{{ $item->memberTypeLabel() }}</span></div>
                <div class="review-row"><span class="review-label">Unit</span><span class="review-value">{{ $item->unit_number }}</span></div>
                <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $item->mobile }}</span></div>
                <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $item->email ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Moving In</span><span class="review-value">{{ $item->move_in_date?->format('d M Y') ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Received</span><span class="review-value">{{ $item->created_at->format('d M Y, h:i A') }}</span></div>
                @if($item->message)
                    <div style="margin-top: 14px;">
                        <div class="form-label">Message</div>
                        <p style="font-size: 14px; color: var(--text-secondary); white-space: pre-line;">{{ $item->message }}</p>
                    </div>
                @endif
                @if($item->document_path)
                    <a href="{{ route('society.member-requests.document', $item) }}" class="btn btn-secondary btn-sm" style="margin-top: 8px;"><i class="fas fa-paperclip"></i> Download proof</a>
                @endif
                @if($item->status !== 'pending')
                    <div class="info-box" style="margin-top: 16px;">
                        <i class="fas fa-circle-info"></i>
                        <span>
                            {{ $item->statusLabel() }} by {{ $item->reviewer?->name ?? '—' }} on {{ $item->reviewed_at?->format('d M Y') }}{{ $item->review_note ? ': '.$item->review_note : '.' }}
                            @if($item->member)
                                <a href="{{ route('society.members.show', $item->member) }}">Open member record</a>
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div>
        @if($item->status === 'pending')
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Approve</div>
                    <form method="POST" action="{{ route('society.member-requests.approve', $item) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Unit <span class="required">*</span></label>
                            <select name="unit_id" class="form-control" required>
                                <option value="">Select unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((string) old('unit_id', $matchedUnit?->id) === (string) $unit->id)>{{ $unit->unit_number }}{{ $unit->owner_name ? ' · '.$unit->owner_name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($item->request_type === 'transfer')
                            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">The unit's current owner record is marked inactive and the applicant becomes the owner.</div>
                        @endif
                        <div class="form-group">
                            <label class="form-label">Note</label>
                            <input type="text" name="review_note" class="form-control" maxlength="255">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-check"></i> Approve &amp; Add Member</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Reject</div>
                    <form method="POST" action="{{ route('society.member-requests.reject', $item) }}" data-confirm="Reject the request from {{ $item->name }}?" data-confirm-label="Reject">
                        @csrf
                        <div class="form-group">
                            <input type="text" name="review_note" class="form-control" maxlength="255" placeholder="Reason (optional)">
                        </div>
                        <button type="submit" class="btn btn-secondary" style="width: 100%;"><i class="fas fa-xmark"></i> Reject</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
