@extends('society.layouts.app')

@section('title', 'Vendor Management')

@section('content')
@php
    $categoryTints = [
        'Electrical' => 'badge-info',
        'Housekeeping' => 'badge-info',
        'Security' => 'badge-warning',
        'Pest Control' => 'badge-success',
        'Maintenance' => 'badge-warning',
        'Waste Management' => 'badge-info',
        'Plumbing' => 'badge-danger',
    ];
@endphp
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Vendor Management</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Vendor Management</span>
            </div>
        </div>
        <a href="{{ route('society.vendors.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Vendor</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="fas fa-circle-check"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Vendors</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>All registered vendors</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Vendors</div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-trend" style="color: var(--success);"><span>Currently active</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Pending Approval</div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-trend" style="color: var(--warning);"><span>Awaiting approval</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-user-slash"></i></div>
        <div class="stat-info">
            <div class="stat-label">Inactive Vendors</div>
            <div class="stat-value">{{ $stats['inactive'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>Not active</span></div>
        </div>
    </div>
</div>

{{-- Filter card --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.vendors.index') }}">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search vendor name, contact person, email, phone...">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Approval Status</label>
                    <select name="approval_status" class="form-control">
                        <option value="">All Approval Status</option>
                        <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <button type="button" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-sliders"></i> More Filters</button>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px;">
                <a href="{{ route('society.vendors.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
            </div>
        </form>
    </div>
</div>

{{-- Table card --}}
<div class="card">
    <div class="card-body">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div class="section-title" style="font-size: 16px; margin-bottom: 0;">Vendors List</div>
            <div style="display: inline-flex; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm"><i class="fas fa-file-export"></i> Export</button>
                <button type="button" class="btn btn-secondary btn-sm"><i class="fas fa-table-columns"></i> Columns</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vendor Name</th>
                        <th>Category</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Approval Status</th>
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
                                        <span style="font-size: 11px; color: var(--text-muted);">{{ $vendor->vendor_code }}</span>
                                    </span>
                                </div>
                            </td>
                            <td><span class="badge {{ $categoryTints[$vendor->category] ?? 'badge-info' }}">{{ $vendor->category }}</span></td>
                            <td>
                                <span style="font-weight: 600; display: block;">{{ $vendor->contact_person }}</span>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $vendor->designation ?? '—' }}</span>
                            </td>
                            <td style="white-space: nowrap;"><i class="fas fa-phone" style="width: 14px; color: var(--text-muted);"></i> {{ $vendor->phone ?? '—' }}</td>
                            <td>{{ $vendor->email ?? '—' }}</td>
                            <td><span class="badge {{ $vendor->statusBadgeClass() }}">{{ ucfirst($vendor->status) }}</span></td>
                            <td><span class="badge {{ $vendor->approvalBadgeClass() }}">{{ ucfirst($vendor->approval_status) }}</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.vendors.edit', $vendor) }}" class="action-btn" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.vendors.edit', $vendor) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                    <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                                    <div class="empty-state-title">No vendors found</div>
                                    <div class="empty-state-text">Try adjusting your filters or add a new vendor.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $vendors, 'firstLast' => true, 'side' => 2, 'unit' => 'vendors'])
    </div>
</div>
@endsection
