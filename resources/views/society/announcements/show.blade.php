@extends('society.layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $announcement->title }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.notifications.index') }}">Notifications</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ Str::limit($announcement->title, 40) }}</span>
            </div>
        </div>
        <a href="{{ route('society.notifications.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
                    <span class="badge {{ $announcement->priorityBadgeClass() }}">{{ ucfirst($announcement->priority) }} priority</span>
                    @if($announcement->category)<span class="badge badge-secondary">{{ $announcement->category }}</span>@endif
                    <span class="badge badge-info">{{ $announcement->society_id ? $announcement->society?->name : 'All societies' }}</span>
                </div>
                <div style="font-size: 14px; line-height: 1.7; white-space: pre-line;">{{ $announcement->message }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 20px;">
                    Sent {{ $announcement->sent_at?->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Announcements are one-off messages from the platform. Notices that need acknowledgement live under <a href="{{ route('society.notices.index') }}">Notices</a>.</span>
        </div>
    </div>
</div>
@endsection
