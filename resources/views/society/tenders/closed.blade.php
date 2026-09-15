@extends('society.layouts.app')

@section('title', 'Closed Tenders')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Closed Tenders</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Tender Management</span>
                <span class="breadcrumb-separator">/</span>
                <span>Closed Tenders</span>
            </div>
        </div>
        <a href="{{ route('society.tenders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Tender</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;"><i class="fas fa-circle-check"></i> <span>{{ session('success') }}</span></div>
@endif

@include('society.tenders._stats', ['stats' => $stats])

@include('society.tenders._filters', ['action' => route('society.tenders.closed'), 'departments' => $departments, 'tenderTypes' => $tenderTypes])

<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">Closed Tenders</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">List of all tenders that have been closed</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tender Title</th>
                        <th>Reference No.</th>
                        <th>Department</th>
                        <th>Tender Type</th>
                        <th>Awarded Vendor</th>
                        <th class="num" style="text-align: right;">Contract Value (&#8377;)</th>
                        <th>Closed Date</th>
                        <th>Reason</th>
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
                            <td>{{ $tender->awarded_vendor ?? '—' }}</td>
                            <td style="text-align: right;">{{ $tender->contract_value ? number_format((float) $tender->contract_value, 0) : '—' }}</td>
                            <td style="white-space: nowrap;">{{ $tender->closed_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $tender->closed_reason ?? '—' }}</td>
                            <td><span class="badge badge-secondary">Closed</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.tenders.show', $tender) }}" class="action-btn" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.tenders.edit', $tender) }}" class="action-btn" title="Edit"><i class="fas fa-pencil"></i></a>
                                    
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-folder"></i></div>
                                    <div class="empty-state-title">No closed tenders found</div>
                                    <div class="empty-state-text">Closed tenders will appear here.</div>
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
