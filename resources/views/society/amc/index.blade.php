@extends('society.layouts.app')

@section('title', 'AMC & Renewal Tracker')

@php
    $tabs = [
        'all' => 'All Contracts',
        'expiring-soon' => 'Expiring Soon',
        'expired' => 'Expired',
        'by-category' => 'By Category',
        'by-vendor' => 'By Vendor',
    ];
    $badgeColors = ['badge-info', 'badge-success', 'badge-warning', 'badge-danger'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">AMC &amp; Renewal Tracker</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <span>Track all AMC contracts and renewal dates in one place.</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.amc.categories') }}" class="btn btn-secondary"><i class="fas fa-table-cells-large"></i> AMC Categories</a>
            <a href="{{ route('society.amc.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New AMC</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="fas fa-circle-check"></i> <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Tabs --}}
<div class="tabs" style="overflow-x: auto; margin-bottom: 20px;">
    @foreach($tabs as $key => $label)
        <a href="{{ route('society.amc.index', ['tab' => $key]) }}" class="tab {{ $tab === $key ? 'active' : '' }}" style="white-space: nowrap;">{{ $label }}</a>
    @endforeach
</div>

{{-- Stat cards --}}
<div class="stats-grid stats-grid-5">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-file-lines"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Contracts</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>All time</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Contracts</div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span>{{ $stats['active_pct'] }}</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expiring Soon</div>
            <div class="stat-value">{{ $stats['expiring_soon'] }}</div>
            <div class="stat-trend" style="color: var(--orange);"><span>Within 30 days</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-circle-exclamation"></i></div>
        <div class="stat-info">
            <div class="stat-label">Expired Contracts</div>
            <div class="stat-value">{{ $stats['expired'] }}</div>
            <div class="stat-trend" style="color: var(--danger);"><span>Need immediate attention</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Annual Value</div>
            <div class="stat-value">&#8377; {{ $stats['total_value'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Across all contracts</span></div>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.amc.index') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div style="display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr 1fr auto auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Search</label>
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by item name, vendor, contract no...">
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
                    <label class="form-label">Vendor</label>
                    <select name="vendor" class="form-control">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor }}" {{ request('vendor') === $vendor ? 'selected' : '' }}>{{ $vendor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Expiry</label>
                    <select name="expiry" class="form-control">
                        <option value="">All Time</option>
                        <option value="30" {{ request('expiry') === '30' ? 'selected' : '' }}>Next 30 days</option>
                        <option value="60" {{ request('expiry') === '60' ? 'selected' : '' }}>Next 60 days</option>
                        <option value="90" {{ request('expiry') === '90' ? 'selected' : '' }}>Next 90 days</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('society.amc.index', ['tab' => $tab]) }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
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
                        <th>Item / Asset</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Contract No.</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="num" style="text-align: right;">Amount (&#8377;)</th>
                        <th>Status</th>
                        <th>Days Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                        @php
                            $daysLeft = $contract->daysLeft();
                            $daysColor = $daysLeft < 0 ? 'var(--danger)' : ($daysLeft <= 30 ? 'var(--orange)' : 'var(--success)');
                        @endphp
                        <tr>
                            <td>{{ $contracts->firstItem() + $loop->index }}</td>
                            <td>
                                <span style="font-weight: 600; display: block;">{{ $contract->item_asset }}</span>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $contract->item_sub ?? '—' }}</span>
                            </td>
                            <td>
                                @if($contract->category)
                                    <span class="badge {{ $badgeColors[$contract->amc_category_id % count($badgeColors)] }}">{{ $contract->category->name }}</span>
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>{{ $contract->vendor_name ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $contract->contract_no ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $contract->start_date?->format('d M Y') ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $contract->end_date?->format('d M Y') ?? '—' }}</td>
                            <td style="text-align: right;">{{ number_format((float) $contract->amount, 0) }}</td>
                            <td><span class="badge {{ $contract->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $contract->status)) }}</span></td>
                            <td style="font-weight: 600; color: {{ $daysColor }}; white-space: nowrap;">{{ $daysLeft }} days</td>
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
                            <td colspan="11">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-file-contract"></i></div>
                                    <div class="empty-state-title">No AMC contracts found</div>
                                    <div class="empty-state-text">Add your first AMC contract to get started.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $contracts, 'firstLast' => true, 'side' => 2, 'unit' => 'contracts'])
    </div>
</div>
@endsection
