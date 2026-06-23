@extends('superadmin.layouts.app')

@section('title', 'All Societies')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">All Societies</h1>
            <p class="page-subtitle">Manage all registered societies in the platform.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="{{ route('superadmin.societies.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Society</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.societies.index') }}">Society Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>All Societies</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item">
                <div class="header-search" style="max-width: 100%;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search by society name, prefix, email, mobile...">
                </div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select class="form-control">
                    <option>All</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select class="form-control">
                    <option>All</option>
                    <option>Active</option>
                    <option>Expiring Soon</option>
                    <option>Expired</option>
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <select class="form-control">
                    <option>All</option>
                    <option>Basic Plan</option>
                    <option>Standard Plan</option>
                    <option>Premium Plan</option>
                </select>
            </div>
            <div class="filter-item" style="flex: 0 0 auto;">
                <div class="header-search" style="max-width: 180px;">
                    <i class="fas fa-calendar search-icon"></i>
                    <input type="text" placeholder="Select date range">
                </div>
            </div>
            <div class="filter-item" style="flex: 0 0 auto; display: flex; gap: 8px;">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                <button class="btn btn-outline-secondary btn-sm">Reset</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Society Name</th>
                        <th>Prefix</th>
                        <th>Contact Details</th>
                        <th>Plan</th>
                        <th>Units</th>
                        <th>Subscription Status</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($societies as $index => $society)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="society-row">
                                <div class="society-icon"><i class="fas fa-building"></i></div>
                                <div class="society-details">
                                    <h4>{{ $society->name }}</h4>
                                    <p>{{ $society->city }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="prefix-tag">{{ $society->prefix }}</span></td>
                        <td>
                            <div style="font-size: 12px;">
                                <div>{{ $society->primary_email }}</div>
                                <div style="color: var(--text-muted);">{{ $society->primary_mobile }}</div>
                            </div>
                        </td>
                        <td>{{ $society->subscriptionPlan->name ?? 'N/A' }}</td>
                        <td>
                            <div style="font-size: 12px;">
                                <div>{{ $society->total_units }}</div>
                                <div style="color: var(--text-muted);">Flats: {{ $society->flats_count }}, Shops: {{ $society->shops_count }}</div>
                            </div>
                        </td>
                        <td>
                            @php
                                $subStatus = $society->subscription_status;
                                $subStatusClass = match($subStatus) {
                                    'active' => 'active',
                                    'expiring_soon' => 'expiring_soon',
                                    'expired' => 'expired',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="status-badge {{ $subStatusClass }}">{{ ucfirst(str_replace('_', ' ', $subStatus)) }}</span>
                        </td>
                        <td><span class="status-badge {{ $society->status }}">{{ ucfirst($society->status) }}</span></td>
                        <td>
                            <div style="font-size: 12px;">
                                <div>{{ $society->subscription_end_date ? $society->subscription_end_date->format('d M Y') : 'N/A' }}</div>
                                @if($society->subscription_end_date)
                                    @php $daysLeft = now()->diffInDays($society->subscription_end_date, false); @endphp
                                    <div style="color: {{ $daysLeft <= 30 ? 'var(--danger)' : 'var(--success)' }}; font-size: 11px;">
                                        {{ $daysLeft > 0 ? $daysLeft . ' days left' : 'Expired' }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="font-size: 12px;">{{ $society->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('superadmin.societies.show', $society) }}" class="action-btn view"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('superadmin.societies.edit', $society) }}" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('superadmin.societies.destroy', $society) }}" method="POST" style="display: inline;" data-confirm="Delete this society?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px;">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-building"></i></div>
                                <div class="empty-state-title">No societies found</div>
                                <div class="empty-state-text">Add your first society to get started.</div>
                                <a href="{{ route('superadmin.societies.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Society</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">Showing {{ $societies->firstItem() ?? 0 }} to {{ $societies->lastItem() ?? 0 }} of {{ $societies->total() }} entries</div>
            <div class="pagination">
                @if($societies->onFirstPage())
                    <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                @else
                    <a href="{{ $societies->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                @endif

                @foreach($societies->getUrlRange(1, $societies->lastPage()) as $page => $url)
                    @if($page == $societies->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($societies->hasMorePages())
                    <a href="{{ $societies->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
            <div>
                <select class="form-control" style="width: auto; min-width: 80px;">
                    <option>10 / page</option>
                    <option>25 / page</option>
                    <option>50 / page</option>
                </select>
            </div>
        </div>
    </div>
</div>
@endsection
