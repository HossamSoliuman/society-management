@extends('society.layouts.app')

@section('title', 'Document Categories')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Document Categories</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.documents.index') }}">Document Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Categories</span>
            </div>
        </div>
        <a href="{{ route('society.documents.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Documents</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;"><i class="fas fa-exclamation-circle"></i> <span>{{ $errors->first() }}</span></div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('society.documents.categories.store') }}" style="display: grid; grid-template-columns: 1fr 1.5fr 160px 140px auto; gap: 12px; align-items: end;">
            @csrf
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">New Category <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Audit Reports" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="Optional">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Icon</label>
                <input type="text" name="icon" class="form-control" value="{{ old('icon', 'fa-folder') }}" placeholder="fa-folder">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
        </form>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-folder"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Categories</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>All categories</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active</div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span>In use</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-circle-pause"></i></div>
        <div class="stat-info">
            <div class="stat-label">Inactive</div>
            <div class="stat-value">{{ $stats['inactive'] }}</div>
            <div class="stat-trend" style="color: var(--orange);"><span>Not in use</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-file-lines"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Documents</div>
            <div class="stat-value">{{ $stats['documents'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Across all categories</span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Documents</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="rli-ico" style="background: var(--primary-light); color: var(--primary);"><i class="fas {{ $category->icon ?? 'fa-folder' }}"></i></span>
                                    <span style="font-weight: 600;">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $category->description ?? '—' }}</td>
                            <td>{{ $category->documents_count }}</td>
                            <td><span class="badge {{ $category->statusBadgeClass() }}">{{ ucfirst($category->status) }}</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);" onclick="document.getElementById('edit-cat-{{ $category->id }}').style.display = document.getElementById('edit-cat-{{ $category->id }}').style.display === 'none' ? 'table-row' : 'none'"><i class="fas fa-pencil"></i></button>
                                    <form method="POST" action="{{ route('society.documents.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Delete" style="color: var(--danger); border-color: var(--danger);"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="edit-cat-{{ $category->id }}" style="display: none; background: var(--gray-50);">
                            <td colspan="6">
                                <form method="POST" action="{{ route('society.documents.categories.update', $category) }}" style="display: grid; grid-template-columns: 1fr 1.5fr 160px 140px auto; gap: 12px; align-items: end;">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group" style="margin-bottom: 0;"><input type="text" name="name" class="form-control" value="{{ $category->name }}" required></div>
                                    <div class="form-group" style="margin-bottom: 0;"><input type="text" name="description" class="form-control" value="{{ $category->description }}"></div>
                                    <div class="form-group" style="margin-bottom: 0;"><input type="text" name="icon" class="form-control" value="{{ $category->icon }}"></div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <select name="status" class="form-control">
                                            <option value="active" {{ $category->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $category->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-folder"></i></div>
                                    <div class="empty-state-title">No categories found</div>
                                    <div class="empty-state-text">Document categories will appear here.</div>
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
