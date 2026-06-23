@extends('superadmin.layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Announcements</h1>
            <p class="page-subtitle">Manage and send announcements to society members.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.notification.announcements.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Send Announcement</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Notifications</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search announcements..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Priorities</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Recipients</th>
                        <th>Priority</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Sent Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($announcements as $ann)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $ann->title }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($ann->message, 60) }}</div>
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $ann->recipient_type)) }} ({{ $ann->estimated_recipients }})</td>
                        <td>
                            @php $pColors = ['normal' => 'secondary', 'high' => 'warning', 'urgent' => 'danger']; @endphp
                            <span class="badge badge-{{ $pColors[$ann->priority] ?? 'secondary' }}">{{ ucfirst($ann->priority) }}</span>
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $ann->delivery_channel)) }}</td>
                        <td><span class="status-badge {{ $ann->status }}">{{ ucfirst($ann->status) }}</span></td>
                        <td>{{ $ann->sent_at ? $ann->sent_at->format('d M Y') : 'N/A' }}</td>
                        <td><div style="display: flex; gap: 4px;"><button class="action-btn view"><i class="fas fa-eye"></i></button><button class="action-btn"><i class="fas fa-ellipsis-h"></i></button></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $announcements])
    </div>
</div>
@endsection
