@extends('superadmin.layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Ticket {{ $ticket->ticket_number }}</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.tickets.index') }}">Support Tickets</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $ticket->ticket_number }}</span>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">{{ $ticket->subject }}</div>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Description</div>
                <div style="font-size: 13px; line-height: 1.6;">{{ $ticket->description }}</div>
            </div>
            <div class="review-row">
                <span class="review-label">Status</span>
                <span class="status-badge {{ $ticket->status }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Priority</span>
                @php $prColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger']; @endphp
                <span class="badge badge-{{ $prColors[$ticket->priority] ?? 'secondary' }}">{{ ucfirst($ticket->priority) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Category</span>
                <span class="review-value">{{ $ticket->category ?? 'N/A' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Society</span>
                <span class="review-value">{{ $ticket->society->name ?? 'N/A' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Created</span>
                <span class="review-value">{{ $ticket->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Update Status</div></div>
        <div class="card-body">
            <form action="{{ route('superadmin.tickets.status', $ticket) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
</div>
@endsection
