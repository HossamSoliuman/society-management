@extends('society.layouts.app')

@section('title', 'Draft Tenders')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Draft Tenders</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Tender Management</span>
                <span class="breadcrumb-separator">/</span>
                <span>Draft Tenders</span>
            </div>
        </div>
        <a href="{{ route('society.tenders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Tender</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;"><i class="fas fa-circle-check"></i> <span>{{ session('success') }}</span></div>
@endif

@include('society.tenders._stats', ['stats' => $stats])

@include('society.tenders._filters', ['action' => route('society.tenders.draft'), 'departments' => $departments, 'tenderTypes' => $tenderTypes])

<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">Draft Tenders</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">List of all draft tenders that are under preparation</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tender Title</th>
                        <th>Reference No.</th>
                        <th>Department</th>
                        <th>Tender Type</th>
                        <th>Last Modified</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenders as $tender)
                        <tr>
                            <td>{{ $tenders->firstItem() + $loop->index }}</td>
                            <td>
                                <span style="font-weight: 600; display: block;">{{ $tender->title }}</span>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $tender->sub_title ?? '—' }}</span>
                            </td>
                            <td style="white-space: nowrap;">{{ $tender->reference_no }}</td>
                            <td>{{ $tender->department ?? '—' }}</td>
                            <td><span class="badge {{ $tender->typeBadgeClass() }}">{{ ucfirst($tender->tender_type) }}</span></td>
                            <td style="white-space: nowrap;">
                                {{ $tender->updated_at?->format('d M Y') }}
                                <span style="display: block; font-size: 11px; color: var(--text-muted);">{{ $tender->updated_at?->format('h:i A') }}</span>
                            </td>
                            <td>{{ $tender->created_by ?? '—' }}</td>
                            <td><span class="badge {{ $tender->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $tender->status)) }}</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" class="action-btn" title="View"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></button>
                                    <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-pen-to-square"></i></div>
                                    <div class="empty-state-title">No draft tenders found</div>
                                    <div class="empty-state-text">Create a new tender to get started.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('society.partials.pagination', ['items' => $tenders, 'firstLast' => true, 'side' => 2, 'unit' => 'entries'])
    </div>
</div>
@endsection
