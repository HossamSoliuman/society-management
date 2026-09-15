@extends('member.layouts.app')

@section('title', $notice->title)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $notice->title }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.notices.index') }}">Notices</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ Str::limit($notice->title, 40) }}</span>
            </div>
        </div>
        <a href="{{ route('member.notices.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
                    <span class="badge {{ $notice->priorityBadgeClass() }}">{{ ucfirst($notice->priority) }} priority</span>
                    <span class="badge badge-secondary">{{ ucfirst($notice->notice_type) }}</span>
                </div>
                @if($notice->short_description)
                    <p style="font-weight: 600; margin-bottom: 12px;">{{ $notice->short_description }}</p>
                @endif
                <div style="font-size: 14px; line-height: 1.7;">{!! $notice->content !!}</div>
                @if($notice->attach_path)
                    <div style="margin-top: 16px;">
                        <a href="{{ asset('storage/'.$notice->attach_path) }}" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-paperclip"></i> Attachment</a>
                    </div>
                @endif
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 20px;">
                    Published {{ $notice->publish_at?->format('d M Y, h:i A') }}
                    @if($notice->expires_at) · Expires {{ $notice->expires_at->format('d M Y') }} @endif
                </div>
            </div>
        </div>
    </div>
    <div>
        @if($notice->require_acknowledgement)
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Acknowledgement</div>
                    @if($acknowledged)
                        <span class="badge badge-success"><i class="fas fa-check"></i> You have acknowledged this notice</span>
                    @else
                        <form method="POST" action="{{ route('member.notices.acknowledge', $notice) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-check"></i> I have read this notice</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
