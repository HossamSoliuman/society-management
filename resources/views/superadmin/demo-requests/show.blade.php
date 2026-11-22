@extends('superadmin.layouts.app')

@section('title', 'Demo request · '.$demoRequest->society_name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $demoRequest->society_name }}</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $demoRequest->phone) }}" class="btn btn-secondary"><i class="fas fa-phone"></i> Call</a>
            <a href="mailto:{{ $demoRequest->email }}?subject={{ rawurlencode('Your demo request for '.$demoRequest->society_name) }}" class="btn btn-primary"><i class="fas fa-envelope"></i> Email</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.demo-requests.index') }}">Demo Requests</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $demoRequest->society_name }}</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Request details</div>
        </div>
        <div class="card-body">
            <div class="review-row">
                <span class="review-label">Status</span>
                <span class="badge {{ $demoRequest->statusBadgeClass() }}">{{ $demoRequest->statusLabel() }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Name</span>
                <span class="review-value">{{ $demoRequest->name }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Role</span>
                <span class="review-value">{{ $demoRequest->roleLabel() }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Society</span>
                <span class="review-value">{{ $demoRequest->society_name }}, {{ $demoRequest->city }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Flats / units</span>
                <span class="review-value">{{ $demoRequest->units ?? 'Not given' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Phone</span>
                <span class="review-value">{{ $demoRequest->phone }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Email</span>
                <span class="review-value">{{ $demoRequest->email }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Received</span>
                <span class="review-value">{{ $demoRequest->created_at->format('d M Y, h:i A') }}</span>
            </div>
            @if($demoRequest->handled_at)
                <div class="review-row">
                    <span class="review-label">Last updated by</span>
                    <span class="review-value">{{ $demoRequest->handler?->name ?? 'Deleted user' }} · {{ $demoRequest->handled_at->format('d M Y, h:i A') }}</span>
                </div>
            @endif
            @if($demoRequest->message)
                <div style="margin-top: 16px;">
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Message</div>
                    <div style="font-size: 13px; line-height: 1.6; white-space: pre-line;">{{ $demoRequest->message }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Follow-up</div></div>
        <div class="card-body">
            <form action="{{ route('superadmin.demo-requests.update', $demoRequest) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        @foreach($statuses as $key => [$label, $badge])
                            <option value="{{ $key }}" @selected(old('status', $demoRequest->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="admin_note">Internal note</label>
                    <textarea name="admin_note" id="admin_note" rows="5" class="form-control" placeholder="Call outcome, demo date, follow-up">{{ old('admin_note', $demoRequest->admin_note) }}</textarea>
                    @error('admin_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div style="display: flex; gap: 8px; justify-content: space-between; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary">Save follow-up</button>
                </div>
            </form>
            <form method="POST" action="{{ route('superadmin.demo-requests.destroy', $demoRequest) }}" data-confirm="Delete the demo request from {{ $demoRequest->society_name }}?" data-confirm-label="Delete" style="margin-top: 16px;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-trash"></i> Delete request</button>
            </form>
        </div>
    </div>
</div>
@endsection
