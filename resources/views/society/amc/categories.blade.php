@extends('society.layouts.app')

@section('title', 'AMC Categories')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">AMC Categories</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amc.index') }}">AMC &amp; Renewal Tracker</a>
                <span class="breadcrumb-separator">/</span>
                <span>AMC Categories</span>
            </div>
        </div>
        <a href="{{ route('society.amc.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Category</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;"><i class="fas fa-circle-check"></i> <span>{{ session('success') }}</span></div>
@endif

{{-- Stat cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-table-cells-large"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Categories</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>All categories</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Categories</div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span>{{ $stats['active_pct'] }}</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-circle-pause"></i></div>
        <div class="stat-info">
            <div class="stat-label">Inactive Categories</div>
            <div class="stat-value">{{ $stats['inactive'] }}</div>
            <div class="stat-trend" style="color: var(--orange);"><span>{{ $stats['inactive_pct'] }}</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-cube"></i></div>
        <div class="stat-info">
            <div class="stat-label">Assets Covered</div>
            <div class="stat-value">{{ $stats['assets_covered'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Across all categories</span></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.amc.categories') }}">
            <div style="display: grid; grid-template-columns: 1fr 200px auto auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search category...">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('society.amc.categories') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
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
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Assets Covered</th>
                        <th>Status</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="rli-ico" style="background: var(--primary-light); color: var(--primary);"><i class="fas {{ $category->icon ?? 'fa-table-cells-large' }}"></i></span>
                                    <span style="font-weight: 600;">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $category->description ?? '—' }}</td>
                            <td>{{ $category->assets_covered ?? $category->contracts_count }}</td>
                            <td><span class="badge {{ $category->statusBadgeClass() }}">{{ ucfirst($category->status) }}</span></td>
                            <td style="white-space: nowrap;">{{ $category->created_at?->format('d M Y') }}</td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></button>
                                    <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-table-cells-large"></i></div>
                                    <div class="empty-state-title">No categories found</div>
                                    <div class="empty-state-text">Add your first AMC category to get started.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('society.partials.pagination', ['items' => $categories, 'firstLast' => true, 'side' => 2, 'unit' => 'categories'])
    </div>
</div>
@endsection
