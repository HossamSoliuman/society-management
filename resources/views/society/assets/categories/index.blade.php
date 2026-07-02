@extends('society.layouts.app')

@section('title', 'Asset Categories')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Asset Categories</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.assets.index') }}">Assets Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Asset Categories</span>
            </div>
        </div>
        <a href="{{ route('society.assets.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-cube"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Categories</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>All Categories</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Active Categories</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>{{ $stats['active_pct'] }} of total</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-ban"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactive Categories</div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-trend" style="color: var(--danger);"><span>{{ $stats['inactive_pct'] }} of total</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Assets</div>
                    <div class="stat-value">{{ $stats['total_assets'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Across all categories</span></div>
                </div>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.assets.categories.index') }}">
                    <div style="display: grid; grid-template-columns: 1fr 200px auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search categories...">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.assets.categories.index') }}" class="btn btn-secondary"><i class="fas fa-file-export"></i> Export</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table card --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Total Assets</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                @php [$bg, $fg] = \App\Models\AssetCategory::tint($category->color); @endphp
                                <tr>
                                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $category->icon }}"></i></span>
                                            <span style="font-weight: 600;">{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-secondary);">{{ $category->description }}</td>
                                    <td style="font-weight: 600;">{{ $category->assets_count }}</td>
                                    <td><span class="badge {{ $category->statusBadgeClass() }}">{{ $category->statusLabel() }}</span></td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.assets.categories.edit', $category) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-cube"></i></div>
                                            <div class="empty-state-title">No categories found</div>
                                            <div class="empty-state-text">Add your first asset category to get started.</div>
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
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Category Overview --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Category Overview</div>
                @include('society.partials.donut', [
                    'segments' => collect($overview['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                    'centerValue' => $overview['center_value'],
                    'centerLabel' => $overview['center_label'],
                    'size' => 170,
                    'stroke' => 14,
                ])
                <div class="chart-legend" style="margin-top: 20px;">
                    @foreach($overview['segments'] as $seg)
                        <div class="legend-item">
                            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                            <span class="legend-label">{{ $seg['label'] }}</span>
                            <span class="legend-value">{{ $seg['value'] }} ({{ $seg['pct'] }})</span>
                        </div>
                    @endforeach
                    <div class="legend-item" style="border-top: 1px solid var(--border-color); margin-top: 6px; padding-top: 10px;">
                        <span class="legend-dot" style="background: var(--gray-400);"></span>
                        <span class="legend-label">Total</span>
                        <span class="legend-value">{{ $stats['total'] }} (100%)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add New Category', 'desc' => 'Create a new asset category', 'url' => route('society.assets.categories.create'), 'color' => 'orange'],
            ['icon' => 'fa-cube', 'label' => 'Manage Assets', 'desc' => 'Go to all assets', 'url' => route('society.assets.index'), 'color' => 'green'],
            ['icon' => 'fa-chart-line', 'label' => 'Asset Report', 'desc' => 'View asset reports', 'url' => route('society.assets.index'), 'color' => 'blue'],
        ]])

        {{-- Tips --}}
        <div class="tips-card">
            <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">Tips</div>
            <ul style="list-style: none; margin: 0; padding: 0;">
                <li><i class="fas fa-circle-check"></i><span>Group similar assets under a single category.</span></li>
                <li><i class="fas fa-circle-check"></i><span>Use short, unique codes for quick reference.</span></li>
                <li><i class="fas fa-circle-check"></i><span>Deactivate categories you no longer use.</span></li>
            </ul>
        </div>
    </div>
</div>
@endsection
