@extends('member.layouts.app')

@section('title', 'Notices')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Notices</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Notices</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('member.notices.index') }}" style="display: grid; grid-template-columns: 1fr auto auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <div class="header-search" style="max-width: none;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search notices...">
                </div>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('member.notices.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>
    </div>
</div>

@forelse($notices as $notice)
    @php($acked = $notice->acknowledgements->isNotEmpty())
    <div class="card">
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start;">
                <div style="flex: 1;">
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        @if($notice->pin_to_dashboard)<i class="fas fa-thumbtack" style="color: var(--orange);" title="Pinned"></i>@endif
                        <a href="{{ route('member.notices.show', $notice) }}" style="font-weight: 600; font-size: 15px;">{{ $notice->title }}</a>
                        <span class="badge {{ $notice->priorityBadgeClass() }}">{{ ucfirst($notice->priority) }}</span>
                        <span class="badge badge-secondary">{{ ucfirst($notice->notice_type) }}</span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px;">{{ $notice->short_description ?: Str::limit(strip_tags($notice->content), 160) }}</div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                        Published {{ $notice->publish_at?->format('d M Y') }}
                        @if($notice->expires_at) · Expires {{ $notice->expires_at->format('d M Y') }} @endif
                    </div>
                </div>
                <div style="flex-shrink: 0;">
                    @if($notice->require_acknowledgement)
                        @if($acked)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Acknowledged</span>
                        @else
                            <form method="POST" action="{{ route('member.notices.acknowledge', $notice) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Acknowledge</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card"><div class="card-body" style="text-align: center; color: var(--text-muted); padding: 32px;">No notices right now.</div></div>
@endforelse

@include('society.partials.pagination', ['items' => $notices, 'unit' => 'notices'])
@endsection
