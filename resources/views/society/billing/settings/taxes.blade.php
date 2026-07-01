@extends('society.layouts.app')

@section('title', 'Taxes')

@php
    $totalTaxes = $stats['total'] ?: 1;
    $activePct = number_format(($stats['active'] / $totalTaxes) * 100, 0);
    $lastUpdated = $stats['last_updated'] ? \Illuminate\Support\Carbon::parse($stats['last_updated'])->format('d M Y') : '—';
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Taxes</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Bill Settings</a>
                <span class="breadcrumb-separator">/</span>
                <span>Taxes</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary"><i class="fas fa-save"></i> Save Tax Settings</button>
    </div>
</div>

@include('society.billing.settings._tabs')

{{-- KPI cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    @include('society.partials.stat-card', ['icon' => 'fa-file-invoice', 'iconVariant' => 'purple', 'label' => 'Total Taxes', 'value' => number_format($stats['total']), 'trend' => 'Configured Taxes', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Active Taxes', 'value' => number_format($stats['active']), 'trend' => $activePct.'% of total', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-percent', 'iconVariant' => 'danger', 'label' => 'Total Tax Rate', 'value' => number_format($stats['total_rate'], 2).'%', 'trend' => 'Overall Tax Percentage', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-calendar-days', 'iconVariant' => 'info', 'label' => 'Last Updated', 'value' => $lastUpdated, 'trend' => 'By Super Admin', 'trendType' => 'muted'])
</div>

<div class="settings-2col">
    {{-- Tax Settings --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title" style="font-size: 16px;">Tax Settings</div>
            <div class="card-subtitle">Configure and manage taxes that will be applied on maintenance bills.</div>

            <div class="setting-toggle-row">
                <div class="setting-text">
                    <div class="label">Enable Taxes</div>
                    <div class="help">Taxes will be applied on bill charges</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-check">
                    <input type="radio" name="apply_scope" class="form-check-input" checked>
                    <span class="form-check-label">On Total Bill Amount (Including Other Charges)</span>
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">Rounding Method</label>
                <select class="form-control">
                    <option>Round Off to Nearest Rupee</option>
                    <option>Round Up</option>
                    <option>Round Down</option>
                    <option>No Rounding</option>
                </select>
                <div class="form-text">Select how tax amount should be rounded off</div>
            </div>
        </div>
    </div>

    {{-- Tax List --}}
    <div class="card">
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                <div>
                    <div class="card-title" style="font-size: 16px;">Tax List</div>
                    <div class="card-subtitle" style="margin-bottom: 0;">Manage all taxes that applies to bills.</div>
                </div>
                <button type="button" class="btn btn-primary" onclick="resetTaxForm(); document.getElementById('taxFormCard').scrollIntoView({behavior:'smooth'});"><i class="fas fa-plus"></i> Add Tax</button>
            </div>

            <div class="table-responsive" style="margin-top: 12px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="padding-left: 8px;">#</th>
                            <th>Tax Name</th>
                            <th>Tax Type</th>
                            <th>Rate (%)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taxes as $index => $tax)
                            <tr>
                                <td style="padding-left: 8px; color: var(--text-muted);"><i class="fas fa-grip-vertical" style="color: var(--gray-300); margin-right: 6px; cursor: grab;"></i>{{ $index + 1 }}</td>
                                <td style="font-weight: 600;">{{ $tax->name }}</td>
                                <td>{{ $tax->taxTypeLabel() }}</td>
                                <td>{{ number_format($tax->rate, 2) }}%</td>
                                <td><span class="status-badge {{ $tax->status === 'active' ? 'active' : 'overdue' }}">{{ ucfirst($tax->status) }}</span></td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="action-btn edit" title="Edit" onclick='editTax(@json($tax))'><i class="fas fa-pencil"></i></button>
                                        <form method="POST" action="{{ route('society.billing.settings.taxes.destroy', $tax) }}" onsubmit="return confirm('Delete this tax?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><div class="empty-state-title">No taxes configured</div></div></td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="font-weight: 700; padding-left: 8px;">Total Tax Rate</td>
                            <td colspan="3" style="font-weight: 700;">{{ number_format($stats['total_rate'], 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Tax --}}
<div class="card" id="taxFormCard">
    <div class="card-body">
        <div class="card-title" style="font-size: 16px;">Add / Edit Tax</div>
        <div class="card-subtitle">Create or update tax details.</div>

        <form method="POST" id="taxForm" action="{{ route('society.billing.settings.taxes.store') }}">
            @csrf
            <input type="hidden" name="_method" id="taxMethod" value="POST">
            <div class="settings-3col-uneven" style="grid-template-columns: 1fr 1fr 340px;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Tax Name <span class="required">*</span></label>
                        <input type="text" name="name" id="tax_name" class="form-control" placeholder="CGST (Central GST)" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Type <span class="required">*</span></label>
                        <select name="tax_type" id="tax_type" class="form-control" required>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Rate (%) <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="rate" id="tax_rate" class="form-control" placeholder="9.00" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Enter tax rate in percentage</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" id="tax_description" class="form-control" rows="2" placeholder="Central Goods and Services Tax"></textarea>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label class="form-label">Apply On</label>
                        <select name="apply_on" id="tax_apply_on" class="form-control">
                            <option value="All Charge Heads">All Charge Heads</option>
                            @foreach($chargeHeads as $ch)
                                <option value="{{ $ch->name }}">{{ $ch->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select charge heads on which this tax will be applied</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Slab (Optional)</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="number" step="0.01" name="slab_from" id="tax_slab_from" class="form-control" placeholder="0.00">
                            <span style="color: var(--text-muted);">To</span>
                            <input type="number" step="0.01" name="slab_to" id="tax_slab_to" class="form-control" placeholder="No Limit">
                        </div>
                        <div class="form-text">Leave blank for no slab limit</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="hidden" name="status" value="inactive">
                            <label class="toggle-switch">
                                <input type="checkbox" id="tax_status" name="status" value="active" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-size: 13px;">Active</span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Tax</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetTaxForm()">Cancel</button>
                    </div>
                </div>
                <div>
                    <div class="info-box" style="flex-direction: column; align-items: stretch; gap: 14px;">
                        <div style="font-weight: 700; display: flex; align-items: center; gap: 8px;"><i class="fas fa-circle-info"></i> How Taxes Work</div>
                        <div style="display: flex; gap: 8px;"><i class="fas fa-list-ol"></i><span>Taxes will be applied in the order listed in the tax list.</span></div>
                        <div style="display: flex; gap: 8px;"><i class="fas fa-layer-group"></i><span>If multiple taxes are added, they will be applied sequentially.</span></div>
                        <div style="display: flex; gap: 8px;"><i class="fas fa-arrows-up-down-left-right"></i><span>You can change the tax order by dragging tax items in the list.</span></div>
                        <div style="display: flex; gap: 8px;"><i class="fas fa-ban"></i><span>Disabled taxes will not be applied on bills.</span></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function editTax(data) {
        document.getElementById('taxForm').action = '{{ url('society/billing/settings/taxes') }}/' + data.id;
        document.getElementById('taxMethod').value = 'PUT';
        document.getElementById('tax_name').value = data.name ?? '';
        document.getElementById('tax_type').value = data.tax_type ?? 'percentage';
        document.getElementById('tax_rate').value = data.rate ?? '';
        document.getElementById('tax_description').value = data.description ?? '';
        document.getElementById('tax_apply_on').value = data.apply_on ?? 'All Charge Heads';
        document.getElementById('tax_slab_from').value = data.slab_from ?? '';
        document.getElementById('tax_slab_to').value = data.slab_to ?? '';
        document.getElementById('tax_status').checked = (data.status ?? 'active') === 'active';
        document.getElementById('taxFormCard').scrollIntoView({behavior: 'smooth'});
    }
    function resetTaxForm() {
        const form = document.getElementById('taxForm');
        form.reset();
        form.action = '{{ route('society.billing.settings.taxes.store') }}';
        document.getElementById('taxMethod').value = 'POST';
        document.getElementById('tax_status').checked = true;
    }
</script>
@endpush
@endsection
