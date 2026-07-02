@extends('society.layouts.app')

@section('title', 'Assets Management')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Assets Management</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Assets Management</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <form method="POST" action="{{ route('society.assets.import') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-secondary"><i class="fas fa-file-import"></i> Import Assets</button>
            </form>
            <a href="{{ route('society.assets.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Asset</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-cube"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Assets</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>All Assets</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">In Use</div>
                    <div class="stat-value">{{ $stats['in_use'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>{{ $stats['in_use_pct'] }} of total</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-screwdriver-wrench"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Under Maintenance</div>
                    <div class="stat-value">{{ $stats['under_maintenance'] }}</div>
                    <div class="stat-trend" style="color: var(--warning);"><span>{{ $stats['under_maintenance_pct'] }} of total</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-box-archive"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactive / Disposed</div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-trend" style="color: var(--danger);"><span>{{ $stats['inactive_pct'] }} of total</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Asset Value</div>
                    <div class="stat-value">&#8377; {{ $stats['total_value'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Book Value (Approx.)</span></div>
                </div>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.assets.index') }}">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search assets...">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="location" class="form-control">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" {{ request('location') === $location ? 'selected' : '' }}>{{ $location }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                @foreach($statuses as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.assets.index') }}" class="btn btn-secondary"><i class="fas fa-file-export"></i> Export</a>
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
                                <th>Asset Name</th>
                                <th>Asset ID</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Purchase Date</th>
                                <th>Value (&#8377;)</th>
                                <th>Status</th>
                                <th>Condition</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                                @php [$bg, $fg] = \App\Models\AssetCategory::tint($asset->category?->color ?? 'gray'); @endphp
                                <tr>
                                    <td>{{ $assets->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="width: 40px; height: 40px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $asset->category?->icon ?? 'fa-cube' }}"></i></span>
                                            <div style="min-width: 0;">
                                                <div style="font-weight: 600;">{{ $asset->name }}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $asset->brand }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $asset->asset_code }}</td>
                                    <td>
                                        @if($asset->category)
                                            <span class="badge badge-secondary">{{ $asset->category->name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $asset->location ?? '—' }}</td>
                                    <td style="white-space: nowrap;">{{ $asset->purchase_date?->format('d M Y') ?? '—' }}</td>
                                    <td style="font-weight: 600; white-space: nowrap;">&#8377; {{ number_format((float) $asset->purchase_cost, 0) }}</td>
                                    <td><span class="status-badge {{ $asset->statusBadgeClass() }}">{{ $asset->statusLabel() }}</span></td>
                                    <td><span class="cond {{ $asset->condition }}">{{ ucfirst($asset->condition) }}</span></td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.assets.edit', $asset) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('society.assets.edit', $asset) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-cube"></i></div>
                                            <div class="empty-state-title">No assets found</div>
                                            <div class="empty-state-text">Try adjusting your filters or add a new asset.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $assets, 'firstLast' => false, 'side' => 2, 'unit' => 'assets'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Asset Categories --}}
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Asset Categories</div>
                    <a href="{{ route('society.assets.categories.index') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All</a>
                </div>
                @foreach($railCategories as $category)
                    @php [$bg, $fg] = \App\Models\AssetCategory::tint($category->color); @endphp
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $category->icon }}"></i></span>
                        <div class="rli-main"><div class="rli-title">{{ $category->name }}</div></div>
                        <span class="rli-meta">{{ $category->assets_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add New Asset', 'desc' => 'Register a new asset', 'url' => route('society.assets.create'), 'color' => 'orange'],
            ['icon' => 'fa-screwdriver-wrench', 'label' => 'Schedule Maintenance', 'desc' => 'Plan maintenance for assets', 'url' => route('society.assets.index', ['status' => 'under_maintenance']), 'color' => 'green'],
            ['icon' => 'fa-clipboard-check', 'label' => 'Asset Audit', 'desc' => 'Perform asset verification', 'url' => route('society.assets.index'), 'color' => 'blue'],
            ['icon' => 'fa-chart-line', 'label' => 'Asset Report', 'desc' => 'View asset reports', 'url' => route('society.assets.index'), 'color' => 'purple'],
        ]])
    </div>
</div>
@endsection
