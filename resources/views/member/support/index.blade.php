@extends('member.layouts.app')

@section('title', 'Complaints & Requests')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Complaints & Requests</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Complaints & Requests</span>
            </div>
        </div>
        <a href="{{ route('member.support.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Raise a Request</a>
    </div>
</div>

<div class="stats-grid stats-grid-3">
    @include('society.partials.stat-card', ['icon' => 'fa-envelope-open', 'iconVariant' => 'warning', 'label' => 'Open', 'value' => $stats['open']])
    @include('society.partials.stat-card', ['icon' => 'fa-spinner', 'iconVariant' => 'info', 'label' => 'In Progress', 'value' => $stats['in_progress']])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Resolved', 'value' => $stats['resolved']])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('member.support.index') }}" style="display: flex; gap: 12px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('member.support.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Request</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Raised</th>
                        <th>Last update</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td style="padding-left: 20px;"><a href="{{ route('member.support.show', $ticket) }}" style="font-weight: 600;">{{ $ticket->ticket_number }}</a></td>
                            <td>{{ Str::limit($ticket->subject, 50) }}</td>
                            <td><span class="badge {{ $ticket->categoryBadgeClass() }}">{{ $ticket->category }}</span></td>
                            <td><span class="badge {{ $ticket->priorityBadgeClass() }}">{{ $ticket->priorityLabel() }}</span></td>
                            <td>{{ $ticket->raised_at?->format('d M Y') }}</td>
                            <td>{{ ($ticket->last_reply_at ?? $ticket->updated_at)?->diffForHumans() }}</td>
                            <td><span class="badge {{ $ticket->statusBadgeClass() }}">{{ $ticket->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">You have not raised any requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $tickets, 'unit' => 'requests'])
@endsection
