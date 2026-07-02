@extends('society.layouts.app')

@section('title', 'Expense Categories')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Expense Categories</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <span>Expense Categories</span>
            </div>
        </div>
        <a href="{{ route('society.expenses.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Category</a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Categories</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Active Categories</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Active Categories</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>Visible in Expense Entry</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-ban"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactive Categories</div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Not in use</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-tag"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Top Category</div>
                    <div class="stat-value">{{ $stats['top'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Most Used</span></div>
                </div>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.expenses.categories.index') }}">
                    <div style="display: grid; grid-template-columns: 1fr 200px auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search category name or description...">
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
                        <a href="{{ route('society.expenses.categories.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
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
                                <th>Status</th>
                                <th>Created On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                @php [$bg, $fg] = \App\Models\ExpenseCategory::tint($category->color); @endphp
                                <tr>
                                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $category->icon }}"></i></span>
                                            <span style="font-weight: 600;">{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-secondary);">{{ $category->description }}</td>
                                    <td><span class="badge {{ $category->statusBadgeClass() }}">{{ $category->statusLabel() }}</span></td>
                                    <td style="white-space: nowrap;">{{ $category->created_at?->format('d M Y') }}</td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.expenses.categories.edit', $category) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-layer-group"></i></div>
                                            <div class="empty-state-title">No categories found</div>
                                            <div class="empty-state-text">Add your first expense category to get started.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $categories, 'firstLast' => true, 'side' => 2, 'unit' => 'entries'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Category Usage --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Category Usage <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">(This Month)</span></div>
                @include('society.partials.donut', [
                    'segments' => collect($usage['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                    'centerValue' => $usage['center_value'],
                    'centerLabel' => $usage['center_label'],
                    'size' => 170,
                    'stroke' => 14,
                ])
                <div class="chart-legend" style="margin-top: 20px;">
                    @foreach($usage['segments'] as $seg)
                        <div class="legend-item">
                            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                            <span class="legend-label">{{ $seg['label'] }}</span>
                            <span class="legend-value">{!! $seg['amount'] !!} ({{ $seg['pct'] }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add New Category', 'desc' => 'Create a new expense category', 'url' => route('society.expenses.categories.create'), 'color' => 'orange'],
            ['icon' => 'fa-store', 'label' => 'Manage Vendors', 'desc' => 'Add or manage vendors', 'url' => route('society.expenses.vendors.index'), 'color' => 'green'],
            ['icon' => 'fa-receipt', 'label' => 'View Expenses', 'desc' => 'Go to all expenses', 'url' => route('society.expenses.index'), 'color' => 'blue'],
        ]])

        {{-- Note --}}
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Categories help you organize and track your society expenses better.</span>
        </div>
    </div>
</div>
@endsection
