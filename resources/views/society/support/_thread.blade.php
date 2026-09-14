@php
    /**
     * Shared reply thread + reply form for support tickets.
     *
     * @var \App\Models\SupportTicket $ticket
     * @var string $action      reply route
     * @var array<string, string> $statuses
     */
@endphp
<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">Conversation ({{ $ticket->replies->count() }})</div>

        @forelse($ticket->replies as $reply)
            @php($mine = $reply->user_id === auth()->id())
            <div style="display: flex; gap: 12px; margin-bottom: 16px; {{ $mine ? 'flex-direction: row-reverse;' : '' }}">
                <span class="rli-ico" style="background: {{ $mine ? 'var(--primary-light)' : 'var(--gray-100)' }}; color: {{ $mine ? 'var(--primary)' : 'var(--text-secondary)' }}; flex-shrink: 0;">
                    <i class="fas {{ $reply->user?->hasRole('super_admin') ? 'fa-headset' : 'fa-user' }}"></i>
                </span>
                <div style="max-width: 80%; background: {{ $mine ? 'var(--primary-light)' : 'var(--gray-50)' }}; border-radius: var(--radius); padding: 10px 14px;">
                    <div style="font-size: 12px; font-weight: 600;">{{ $reply->user?->name ?? 'User' }}
                        <span style="font-weight: 400; color: var(--text-muted);"> · {{ $reply->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div style="font-size: 13px; margin-top: 4px; white-space: pre-line;">{{ $reply->message }}</div>
                    @if($reply->attachment)
                        <a href="{{ asset('storage/'.$reply->attachment) }}" target="_blank" style="font-size: 12px;"><i class="fas fa-paperclip"></i> Attachment</a>
                    @endif
                </div>
            </div>
        @empty
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">No replies yet.</div>
        @endforelse

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" style="border-top: 1px solid var(--border-color); padding-top: 16px;">
            @csrf
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 12px;"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif
            <div class="form-group">
                <label class="form-label">Reply</label>
                <textarea name="message" class="form-control" rows="3" placeholder="Write a reply..." required>{{ old('message') }}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Update status</label>
                    <select name="status" class="form-control">
                        <option value="">Keep as {{ $ticket->statusLabel() }}</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Attachment</label>
                    <input type="file" name="attachment" class="form-control">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
            </div>
        </form>
    </div>
</div>
