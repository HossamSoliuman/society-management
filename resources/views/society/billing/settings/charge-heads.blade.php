@extends('society.layouts.app')

@section('title', 'Charge Heads')

@php
    $total = $stats['total'] ?: 1;
    $activePct = number_format(($stats['active'] / $total) * 100, 1);
    $inactivePct = number_format(($stats['inactive'] / $total) * 100, 1);
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Charge Heads</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Bill Settings</a>
                <span class="breadcrumb-separator">/</span>
                <span>Charge Heads</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary" onclick="openChargeHeadModal()"><i class="fas fa-plus"></i> Add Charge Head</button>
    </div>
</div>

@include('society.billing.settings._tabs')

{{-- KPI cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    @include('society.partials.stat-card', ['icon' => 'fa-list-check', 'iconVariant' => 'primary', 'label' => 'Total Charge Heads', 'value' => number_format($stats['total']), 'trend' => 'All Charge Heads', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Active Charge Heads', 'value' => number_format($stats['active']), 'trend' => $activePct.'% of total', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-ban', 'iconVariant' => 'warning', 'label' => 'Inactive Charge Heads', 'value' => number_format($stats['inactive']), 'trend' => $inactivePct.'% of total', 'trendType' => 'warning'])
    @include('society.partials.stat-card', ['icon' => 'fa-tag', 'iconVariant' => 'purple', 'label' => 'Used in This Month', 'value' => number_format($stats['used']), 'trend' => 'Charge heads', 'trendType' => 'purple'])
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('society.billing.settings.charge-heads') }}">
    <div class="filter-bar">
        <div class="filter-item" style="flex: 2;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search charge head...">
            </div>
        </div>
        <div class="filter-item">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                @foreach(['maintenance' => 'Maintenance', 'utilities' => 'Utilities', 'parking' => 'Parking', 'amenities' => 'Amenities', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('society.billing.settings.charge-heads') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
    </div>
</form>

{{-- Table card --}}
<div class="card">
    <div class="card-body">
        <div class="action-toolbar">
            <div class="action-toolbar-left" style="font-size: 13px; color: var(--text-secondary);">
                Show
                <select class="form-control" style="width: auto; display: inline-block; padding: 4px 8px;" onchange="window.location.href='{{ route('society.billing.settings.charge-heads') }}?per_page='+this.value">
                    @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ (int) request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                entries
            </div>
            <div class="action-toolbar-right">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle"><i class="fas fa-download"></i> Export <i class="fas fa-chevron-down" style="font-size: 9px;"></i></button>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item"><i class="fas fa-file-csv"></i> CSV</a>
                        <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Excel</a>
                        <a href="#" class="dropdown-item"><i class="fas fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-icon"><i class="fas fa-gear"></i></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">#</th>
                        <th>Charge Head Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Calculation Type</th>
                        <th style="text-align: right;">Default Amount (&#8377;)</th>
                        <th>Status</th>
                        <th>Used In</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chargeHeads as $index => $chargeHead)
                        <tr>
                            <td style="padding-left: 20px; color: var(--text-muted);">{{ $chargeHeads->firstItem() + $index }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $chargeHead->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $chargeHead->description }}</div>
                            </td>
                            <td><span class="badge {{ $chargeHead->categoryBadgeClass() }}">{{ $chargeHead->categoryLabel() }}</span></td>
                            <td>{{ $chargeHead->typeLabel() }}</td>
                            <td>{{ $chargeHead->calculationTypeLabel() }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ number_format($chargeHead->default_amount, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $chargeHead->status === 'active' ? 'active' : 'overdue' }}">{{ ucfirst($chargeHead->status) }}</span>
                            </td>
                            <td>{{ $chargeHead->status === 'active' ? $chargeHead->applies_to : '—' }}</td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="action-btn edit" title="Edit" onclick='openChargeHeadModal(@json($chargeHead))'><i class="fas fa-pencil"></i></button>
                                    <button type="button" class="action-btn view" title="View" onclick='openChargeHeadModal(@json($chargeHead))'><i class="fas fa-eye"></i></button>
                                    <div class="dropdown">
                                        <button type="button" class="action-btn dropdown-toggle"><i class="fas fa-ellipsis-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            <form method="POST" action="{{ route('society.billing.settings.charge-heads.destroy', $chargeHead) }}" onsubmit="return confirm('Delete this charge head?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item" style="color: var(--danger);"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-list-check"></i></div>
                                    <div class="empty-state-title">No charge heads found</div>
                                    <div class="empty-state-text">Try adjusting your filters or add a new charge head.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $chargeHeads, 'firstLast' => true, 'side' => 4, 'unit' => 'entries'])

{{-- Add / Edit modal --}}
<div class="modal-overlay" id="chargeHeadModal" style="display: none;">
    <div class="modal-content">
        <form method="POST" id="chargeHeadForm" action="{{ route('society.billing.settings.charge-heads.store') }}">
            @csrf
            <input type="hidden" name="_method" id="chargeHeadMethod" value="POST">
            <div class="modal-header">
                <div class="card-title" id="chargeHeadModalTitle" style="font-size: 16px;">Add Charge Head</div>
                <button type="button" class="btn-icon" onclick="closeChargeHeadModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Charge Head Name <span class="required">*</span></label>
                    <input type="text" name="name" id="ch_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" id="ch_description" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category" id="ch_category" class="form-control" required>
                            <option value="maintenance">Maintenance</option>
                            <option value="utilities">Utilities</option>
                            <option value="parking">Parking</option>
                            <option value="amenities">Amenities</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span class="required">*</span></label>
                        <select name="type" id="ch_type" class="form-control" required>
                            <option value="recurring">Recurring</option>
                            <option value="one_time">One-time</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Calculation Type <span class="required">*</span></label>
                        <select name="calculation_type" id="ch_calc" class="form-control" required>
                            <option value="per_flat">Per Flat</option>
                            <option value="per_sqft">Per Sqft</option>
                            <option value="per_slot">Per Slot</option>
                            <option value="fixed">Fixed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Amount <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" step="0.01" name="default_amount" id="ch_amount" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Applies To</label>
                    <input type="text" name="applies_to" id="ch_applies" class="form-control" value="All Bills">
                </div>
                <div class="setting-toggle-row" style="border: none; padding: 0;">
                    <div class="setting-text">
                        <div class="label">Status</div>
                        <div class="help">Active charge heads appear on bills.</div>
                    </div>
                    <input type="hidden" name="status" value="inactive">
                    <label class="toggle-switch">
                        <input type="checkbox" id="ch_status" name="status" value="active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeChargeHeadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openChargeHeadModal(data = null) {
        const form = document.getElementById('chargeHeadForm');
        const statusToggle = document.getElementById('ch_status');
        if (data) {
            document.getElementById('chargeHeadModalTitle').textContent = 'Edit Charge Head';
            form.action = '{{ url('society/billing/settings/charge-heads') }}/' + data.id;
            document.getElementById('chargeHeadMethod').value = 'PUT';
            document.getElementById('ch_name').value = data.name ?? '';
            document.getElementById('ch_description').value = data.description ?? '';
            document.getElementById('ch_category').value = data.category ?? 'maintenance';
            document.getElementById('ch_type').value = data.type ?? 'recurring';
            document.getElementById('ch_calc').value = data.calculation_type ?? 'per_flat';
            document.getElementById('ch_amount').value = data.default_amount ?? '';
            document.getElementById('ch_applies').value = data.applies_to ?? '';
            statusToggle.checked = (data.status ?? 'active') === 'active';
        } else {
            document.getElementById('chargeHeadModalTitle').textContent = 'Add Charge Head';
            form.action = '{{ route('society.billing.settings.charge-heads.store') }}';
            document.getElementById('chargeHeadMethod').value = 'POST';
            form.reset();
            statusToggle.checked = true;
        }
        document.getElementById('chargeHeadModal').style.display = 'flex';
    }
    function closeChargeHeadModal() {
        document.getElementById('chargeHeadModal').style.display = 'none';
    }
    @if($errors->any())
        openChargeHeadModal();
    @endif
</script>
@endpush
@endsection
