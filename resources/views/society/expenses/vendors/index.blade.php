@extends('society.layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Vendors</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <span>Vendors</span>
            </div>
        </div>
        <a href="{{ route('society.expenses.vendors.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Vendor</a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-store"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Vendors</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Registered vendors</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Active Vendors</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>Available for expenses</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-ban"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactive Vendors</div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Not in use</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-award"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Top Vendor</div>
                    <div class="stat-value" style="font-size: 16px;">{{ $stats['top'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Most used</span></div>
                </div>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.expenses.vendors.index') }}">
                    <div style="display: grid; grid-template-columns: 1fr 200px 200px auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search vendor name, company, phone or email...">
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
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.expenses.vendors.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
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
                                <th>Vendor</th>
                                <th>Contact</th>
                                <th>GST No.</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                                <tr>
                                    <td>{{ $vendors->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span class="rli-ico" style="background: var(--primary-light); color: var(--primary); font-weight: 600;">{{ $vendor->initials() }}</span>
                                            <span>
                                                <span style="font-weight: 600; display: block;">{{ $vendor->name }}</span>
                                                <span style="font-size: 11px; color: var(--text-muted);">{{ $vendor->company }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px;"><i class="fas fa-phone" style="width: 14px; color: var(--text-muted);"></i> {{ $vendor->phone ?? '—' }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><i class="fas fa-envelope" style="width: 14px;"></i> {{ $vendor->email ?? '—' }}</div>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $vendor->gst_number ?? '—' }}</td>
                                    <td>
                                        @if($vendor->category)
                                            <span class="badge {{ $vendor->category->badgeClass() }}">{{ $vendor->category->name }}</span>
                                        @else
                                            <span style="color: var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $vendor->statusBadgeClass() }}">{{ $vendor->statusLabel() }}</span></td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.expenses.vendors.edit', $vendor) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-store"></i></div>
                                            <div class="empty-state-title">No vendors found</div>
                                            <div class="empty-state-text">Add your first vendor to get started.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $vendors, 'firstLast' => true, 'side' => 2, 'unit' => 'entries'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Top Vendors --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Top Vendors</div>
                @forelse($vendors->take(5) as $vendor)
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: var(--primary-light); color: var(--primary); font-weight: 600;">{{ $vendor->initials() }}</span>
                        <span class="rli-main">
                            <span class="rli-title">{{ $vendor->name }}</span>
                            <span class="rli-sub">{{ $vendor->category?->name ?? $vendor->company }}</span>
                        </span>
                        <span class="rli-meta"><span class="badge {{ $vendor->statusBadgeClass() }}">{{ $vendor->statusLabel() }}</span></span>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No vendors yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add New Vendor', 'desc' => 'Register a new vendor', 'url' => route('society.expenses.vendors.create'), 'color' => 'orange'],
            ['icon' => 'fa-layer-group', 'label' => 'Expense Categories', 'desc' => 'Manage expense categories', 'url' => route('society.expenses.categories.index'), 'color' => 'green'],
            ['icon' => 'fa-receipt', 'label' => 'View Expenses', 'desc' => 'Go to all expenses', 'url' => route('society.expenses.index'), 'color' => 'blue'],
        ]])

        {{-- Note --}}
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Keep vendor contact and GST details up to date for accurate expense records.</span>
        </div>
    </div>
</div>
@endsection
