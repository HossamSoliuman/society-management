@extends('superadmin.layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Terms & Conditions</h1>
            <p class="page-subtitle">Manage terms and conditions for the platform.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.terms.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Terms & Conditions</span>
    </div>
</div>

<div class="tab-pills">
    <a href="#" class="tab-pill active">All Documents</a>
    <a href="#" class="tab-pill">Member App</a>
    <a href="#" class="tab-pill">Web Portal</a>
    <a href="#" class="tab-pill">Other Documents</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search documents..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Types</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="form-check-input"></th>
                        <th>Document Title</th>
                        <th>Document Type</th>
                        <th>Applies To</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($terms as $term)
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <div style="font-weight: 600;">{{ $term->title }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($term->content, 50) }}</div>
                        </td>
                        <td>
                            @php $docClasses = ['member_app' => 'member_app', 'web_portal' => 'web_portal', 'other_documents' => 'other']; @endphp
                            <span class="doc-badge {{ $docClasses[$term->document_type] ?? 'other' }}">{{ ucfirst(str_replace('_', ' ', $term->document_type)) }}</span>
                        </td>
                        <td>{{ $term->applies_to }}</td>
                        <td>{{ $term->version }}</td>
                        <td><span class="status-badge {{ $term->status }}">{{ ucfirst($term->status) }}</span></td>
                        <td style="font-size: 12px;">{{ $term->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('superadmin.terms.edit', $term) }}" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('superadmin.terms.destroy', $term) }}" method="POST" style="display: inline;" data-confirm="Delete this document?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $terms])
    </div>
</div>
@endsection
