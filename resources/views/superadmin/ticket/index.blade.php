@extends('superadmin.layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Support Tickets</h1>
            <p class="page-subtitle">Manage and resolve support tickets from societies.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Support Tickets</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-envelope-open"></i></div>
        <div class="stat-info">
            <div class="stat-label">Open Tickets</div>
            <div class="stat-value">{{ $openCount }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-spinner"></i></div>
        <div class="stat-info">
            <div class="stat-label">In Progress</div>
            <div class="stat-value">{{ $inProgressCount }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Resolved</div>
            <div class="stat-value">{{ $resolvedCount }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search tickets..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Priorities</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Society</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->ticket_number }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $ticket->subject }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($ticket->description, 50) }}</div>
                        </td>
                        <td>{{ $ticket->society->name ?? 'N/A' }}</td>
                        <td>{{ $ticket->category ?? 'N/A' }}</td>
                        <td>
                            @php $prColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger']; @endphp
                            <span class="badge badge-{{ $prColors[$ticket->priority] ?? 'secondary' }}">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td><span class="status-badge {{ $ticket->status }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                        <td>{{ $ticket->created_at->format('d M Y') }}</td>
                        <td><div style="display: flex; gap: 4px;"><a href="{{ route('superadmin.tickets.show', $ticket) }}" class="action-btn view"><i class="fas fa-eye"></i></a><button class="action-btn"><i class="fas fa-ellipsis-h"></i></button></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $tickets])
    </div>
</div>
@endsection
