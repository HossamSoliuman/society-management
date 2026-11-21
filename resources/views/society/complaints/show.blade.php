@extends('society.layouts.app')

@section('title', 'Complaint '.$complaint->ticket_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $complaint->ticket_number }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.complaints.index') }}">Complaints</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $complaint->ticket_number }}</span>
            </div>
        </div>
        <a href="{{ route('society.complaints.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Complaints</a>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px;">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 0;">{{ $complaint->subject }}</div>
                    <span class="status-badge {{ $complaint->statusBadgeClass() }}">{{ $complaint->statusLabel() }}</span>
                </div>

                <div class="review-row"><span class="review-label">Category</span><span class="review-value"><span class="badge {{ $complaint->categoryBadgeClass() }}">{{ $complaint->category }}</span></span></div>
                <div class="review-row"><span class="review-label">Priority</span><span class="review-value"><span class="badge {{ $complaint->priorityBadgeClass() }}">{{ $complaint->priorityLabel() }}</span></span></div>
                <div class="review-row"><span class="review-label">Resident</span><span class="review-value">
                    @if($complaint->member)
                        <a href="{{ route('society.members.show', $complaint->member) }}">{{ $complaint->raised_by_name ?? $complaint->member->name }}</a>
                    @else
                        {{ $complaint->raised_by_name ?? '—' }}
                    @endif
                </span></div>
                <div class="review-row"><span class="review-label">Unit / Flat</span><span class="review-value">{{ $complaint->flat_no ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $complaint->mobile ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Location</span><span class="review-value">{{ $complaint->location ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Raised On</span><span class="review-value">{{ $complaint->raised_at?->format('d M Y, h:i A') }}{{ $complaint->creator && ! $complaint->creator->hasRole('member') ? ' · logged by '.$complaint->creator->name : '' }}</span></div>
                @if($complaint->resolved_at)
                    <div class="review-row"><span class="review-label">Resolved On</span><span class="review-value">{{ $complaint->resolved_at->format('d M Y, h:i A') }}</span></div>
                @endif

                <div style="margin-top: 20px;">
                    <div class="form-label">Description</div>
                    <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6; white-space: pre-line;">{{ $complaint->description }}</p>
                </div>

                @if($complaint->attachment_path)
                    <div style="margin-top: 16px;">
                        <a href="{{ asset('storage/'.$complaint->attachment_path) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><i class="fas fa-paperclip"></i> View attachment</a>
                    </div>
                @endif
            </div>
        </div>

        @include('society.support._thread', [
            'ticket' => $complaint,
            'action' => route('society.complaints.reply', $complaint),
            'statuses' => $statuses,
        ])
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Assignment</div>
                <form method="POST" action="{{ route('society.complaints.assign', $complaint) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <select name="assigned_to" class="form-control">
                            <option value="">Unassigned</option>
                            @foreach($team as $user)
                                <option value="{{ $user->id }}" @selected($complaint->assigned_to === $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-user-check"></i> Save Assignment</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Status</div>
                <form method="POST" action="{{ route('society.complaints.status', $complaint) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <select name="status" class="form-control">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($complaint->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width: 100%;"><i class="fas fa-arrows-rotate"></i> Update Status</button>
                </form>
            </div>
        </div>

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>{{ $complaint->member?->hasPortalAccess() ? 'The resident is notified in the portal when you reply or change the status.' : 'This resident has no portal login, so replies are kept here for the office.' }}</span>
        </div>
    </div>
</div>
@endsection
