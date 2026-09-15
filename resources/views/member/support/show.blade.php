@extends('member.layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $ticket->ticket_number }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.support.index') }}">Complaints & Requests</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $ticket->ticket_number }}</span>
            </div>
        </div>
        <a href="{{ route('member.support.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="content-grid" style="grid-template-columns: 2fr 1fr;">
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                    <span class="badge {{ $ticket->statusBadgeClass() }}">{{ $ticket->statusLabel() }}</span>
                    <span class="badge {{ $ticket->priorityBadgeClass() }}">{{ $ticket->priorityLabel() }} priority</span>
                    <span class="badge {{ $ticket->categoryBadgeClass() }}">{{ $ticket->category }}</span>
                </div>
                <h2 style="font-size: 17px; font-weight: 700; margin-bottom: 8px;">{{ $ticket->subject }}</h2>
                <div style="font-size: 13px; white-space: pre-line;">{{ $ticket->description }}</div>
                @if($ticket->attachment_path)
                    <div style="margin-top: 12px;">
                        <a href="{{ asset('storage/'.$ticket->attachment_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-paperclip"></i> Attachment</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Thread: residents cannot change status, so render our own reply form --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Conversation ({{ $ticket->replies->count() }})</div>

                @forelse($ticket->replies as $reply)
                    @php($mine = $reply->user_id === auth()->id())
                    <div style="display: flex; gap: 12px; margin-bottom: 16px; {{ $mine ? 'flex-direction: row-reverse;' : '' }}">
                        <span class="rli-ico" style="background: {{ $mine ? 'var(--primary-light)' : 'var(--gray-100)' }}; color: {{ $mine ? 'var(--primary)' : 'var(--text-secondary)' }}; flex-shrink: 0;">
                            <i class="fas {{ $mine ? 'fa-user' : 'fa-building' }}"></i>
                        </span>
                        <div style="max-width: 80%; background: {{ $mine ? 'var(--primary-light)' : 'var(--gray-50)' }}; border-radius: var(--radius); padding: 10px 14px;">
                            <div style="font-size: 12px; font-weight: 600;">{{ $mine ? 'You' : ($reply->user?->name ?? 'Society office') }}
                                <span style="font-weight: 400; color: var(--text-muted);"> · {{ $reply->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div style="font-size: 13px; margin-top: 4px; white-space: pre-line;">{{ $reply->message }}</div>
                            @if($reply->attachment)
                                <a href="{{ asset('storage/'.$reply->attachment) }}" target="_blank" style="font-size: 12px;"><i class="fas fa-paperclip"></i> Attachment</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">No replies yet. The society office will respond here.</div>
                @endforelse

                <form method="POST" action="{{ route('member.support.reply', $ticket) }}" enctype="multipart/form-data" style="border-top: 1px solid var(--border-color); padding-top: 16px;">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger" style="margin-bottom: 12px;"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">{{ $ticket->isOpen() ? 'Reply' : 'Reply (re-opens this request)' }}</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Write a reply..." required>{{ old('message') }}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Details</div>
                <div class="review-row"><span class="review-label">Raised</span><span class="review-value">{{ $ticket->raised_at?->format('d M Y, h:i A') }}</span></div>
                <div class="review-row"><span class="review-label">Location</span><span class="review-value">{{ $ticket->location ?: '—' }}</span></div>
                <div class="review-row"><span class="review-label">Contact via</span><span class="review-value">{{ $ticket->preferred_contact ?: '—' }}</span></div>
                <div class="review-row"><span class="review-label">Last update</span><span class="review-value">{{ ($ticket->last_reply_at ?? $ticket->updated_at)?->diffForHumans() }}</span></div>
                @if($ticket->resolved_at)
                    <div class="review-row"><span class="review-label">Resolved</span><span class="review-value">{{ $ticket->resolved_at->format('d M Y') }}</span></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
