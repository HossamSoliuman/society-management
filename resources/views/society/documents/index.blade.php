@extends('society.layouts.app')

@section('title', 'Document Management')

@php
    $categoryBadge = [
        'blue' => 'badge-info',
        'green' => 'badge-success',
        'red' => 'badge-danger',
        'orange' => 'badge-warning',
        'purple' => 'badge-info',
    ];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Document Management</h1>
            <p style="margin: 4px 0 0; color: var(--text-secondary); font-size: 14px;">Store, organize and manage all society documents in one place.</p>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Document Management</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.documents.categories') }}" class="btn btn-secondary"><i class="fas fa-folder"></i> Document Categories</a>
            <a href="{{ route('society.documents.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Upload Document</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="fas fa-circle-check"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-file-lines"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Documents</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span><i class="fas fa-arrow-up"></i> 18 this month</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-folder"></i></div>
        <div class="stat-info">
            <div class="stat-label">Categories</div>
            <div class="stat-value">{{ $stats['categories'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span>Active categories</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-cloud-arrow-down"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Size</div>
            <div class="stat-value">{{ $stats['total_size'] }}</div>
            <div class="stat-trend" style="color: var(--info);"><span>Storage used</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-download"></i></div>
        <div class="stat-info">
            <div class="stat-label">Downloads (This Month)</div>
            <div class="stat-value">{{ $stats['downloads'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span><i class="fas fa-arrow-up"></i> 24% from last month</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expiring Soon</div>
            <div class="stat-value">{{ $stats['expiring_soon'] }}</div>
            <div class="stat-trend" style="color: var(--warning);"><span>Within 30 days</span></div>
        </div>
    </div>
</div>

{{-- Filter row --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.documents.index') }}">
            <div style="display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr 1.2fr auto auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Search Documents</label>
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by document name, category, description...">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Document Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Uploaded By</label>
                    <select name="uploaded_by" class="form-control">
                        <option value="">All Users</option>
                        @foreach($uploaders as $uploader)
                            <option value="{{ $uploader }}" {{ request('uploaded_by') === $uploader ? 'selected' : '' }}>{{ $uploader }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Date Range</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('society.documents.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Document Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Uploaded By</th>
                        <th>Uploaded On</th>
                        <th>Size</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        @php
                            $daysLeft = $document->expiry_date ? (int) round(now()->startOfDay()->diffInDays($document->expiry_date->startOfDay(), false)) : null;
                            $expiryColor = $daysLeft !== null && $daysLeft <= 30 ? 'var(--danger)' : 'var(--warning)';
                        @endphp
                        <tr>
                            <td>{{ $documents->firstItem() + $loop->index }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="rli-ico" style="background: var(--danger-light); color: var(--danger);"><i class="fas {{ $document->typeIcon() }}"></i></span>
                                    <span>
                                        <span style="font-weight: 600; display: block;">{{ $document->name }}</span>
                                        <span style="font-size: 11px; color: var(--text-muted);">{{ $document->description ?? '—' }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($document->category)
                                    <span class="badge {{ $categoryBadge[$document->category->color] ?? 'badge-info' }}">{{ $document->category->name }}</span>
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>{{ $document->type }}</td>
                            <td>{{ $document->uploaded_by }}</td>
                            <td style="white-space: nowrap;">
                                <div>{{ $document->created_at?->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $document->created_at?->format('h:i A') }}</div>
                            </td>
                            <td style="white-space: nowrap;">{{ $document->size }}</td>
                            <td style="white-space: nowrap;">
                                @if($document->expiry_date)
                                    <div>{{ $document->expiry_date->format('d M Y') }}</div>
                                    <div style="font-size: 11px; color: {{ $expiryColor }};">({{ $daysLeft }} days left)</div>
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.documents.index') }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.documents.index') }}" class="action-btn" title="Download"><i class="fas fa-download"></i></a>
                                    <form method="POST" action="{{ route('society.documents.destroy', $document) }}" onsubmit="return confirm('Delete this document?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Delete" style="color: var(--danger); border-color: var(--danger);"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-file-lines"></i></div>
                                    <div class="empty-state-title">No documents found</div>
                                    <div class="empty-state-text">Try adjusting your filters or upload a new document.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $documents, 'firstLast' => true, 'side' => 2, 'unit' => 'documents'])
    </div>
</div>
@endsection
