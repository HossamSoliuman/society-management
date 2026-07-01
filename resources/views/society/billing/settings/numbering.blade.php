@extends('society.layouts.app')

@section('title', 'Numbering')

@php
    $fyDisplay = str_replace('-', ' - ', $stats['financial_year']);
    $formatOptions = ['YYYY-#####', 'YY-#####', 'YYYYMM-#####', 'YYMM-#####', '#####'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Numbering</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Bill Settings</a>
                <span class="breadcrumb-separator">/</span>
                <span style="color: var(--primary);">Numbering</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary"><i class="fas fa-save"></i> Save Numbering Settings</button>
    </div>
</div>

@include('society.billing.settings._tabs')

{{-- KPI cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    @include('society.partials.stat-card', ['icon' => 'fa-calendar-days', 'iconVariant' => 'purple', 'label' => 'Active Numbering Series', 'value' => number_format($stats['active_series']), 'trend' => 'Total Active Series', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-file-circle-check', 'iconVariant' => 'success', 'label' => 'Unused Numbers (This Month)', 'value' => number_format($stats['unused_this_month']), 'trend' => 'Across all series', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-arrow-right-arrow-left', 'iconVariant' => 'warning', 'label' => 'Last Number Generated', 'value' => $stats['last_generated'] ?? '—', 'trend' => 'On '.$stats['last_generated_date'], 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-calendar', 'iconVariant' => 'info', 'label' => 'Financial Year', 'value' => $fyDisplay, 'trend' => '01 Apr 2025 to 31 Mar 2026', 'trendType' => 'muted'])
</div>

{{-- Numbering Series table --}}
<div class="card">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div class="card-title" style="font-size: 16px;">Numbering Series</div>
                <div class="card-subtitle" style="margin-bottom: 0;">Configure prefix, format and sequence for all document types.</div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-primary" onclick="resetSeriesForm(); document.getElementById('seriesFormCard').scrollIntoView({behavior:'smooth'});"><i class="fas fa-plus"></i> Add Series</button>
                <button type="button" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</button>
            </div>
        </div>

        <div class="table-responsive" style="margin-top: 16px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">#</th>
                        <th>Document Type</th>
                        <th>Prefix</th>
                        <th>Format</th>
                        <th>Next Number</th>
                        <th>Sample</th>
                        <th>Reset Frequency</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($series as $index => $item)
                        <tr>
                            <td style="padding-left: 20px; color: var(--text-muted);">{{ $index + 1 }}</td>
                            <td style="font-weight: 600;">
                                {{ $item->documentTypeLabel() }}
                                @if($item->is_default)<span class="badge badge-info" style="margin-left: 6px;">Default</span>@endif
                            </td>
                            <td><input type="text" class="form-control" style="width: 90px; padding: 6px 8px;" value="{{ $item->prefix }}" readonly></td>
                            <td>
                                <select class="form-control" style="width: 140px; padding: 6px 8px;" disabled>
                                    <option>{{ $item->format }}</option>
                                </select>
                            </td>
                            <td>{{ $item->formattedNextNumber() }}</td>
                            <td style="font-weight: 600;">{{ $item->sampleNumber() }}</td>
                            <td>{{ $item->resetFrequencyLabel() }}</td>
                            <td><span class="status-badge {{ $item->status === 'active' ? 'active' : 'overdue' }}">{{ ucfirst($item->status) }}</span></td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="action-btn edit" title="Edit" onclick='editSeries(@json($item))'><i class="fas fa-pencil"></i></button>
                                    <form method="POST" action="{{ route('society.billing.settings.numbering.destroy', $item) }}" onsubmit="return confirm('Delete this series?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-info" style="margin-top: 12px;">Showing 1 to {{ $series->count() }} of {{ $series->count() }} entries</div>
    </div>
</div>

<div class="settings-2col" style="grid-template-columns: 1.3fr 1fr;">
    {{-- Add / Edit Numbering Series --}}
    <div class="card" id="seriesFormCard">
        <div class="card-body">
            <div class="card-title" style="font-size: 16px;">Add / Edit Numbering Series</div>
            <div class="card-subtitle">Create a new numbering series or update an existing one.</div>

            <form method="POST" id="seriesForm" action="{{ route('society.billing.settings.numbering.store') }}">
                @csrf
                <input type="hidden" name="_method" id="seriesMethod" value="POST">
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Document Type <span class="required">*</span></label>
                        <select name="document_type" id="s_document_type" class="form-control" required>
                            <option value="maintenance_bill">Maintenance Bill</option>
                            <option value="receipt">Receipt</option>
                            <option value="credit_note">Credit Note</option>
                            <option value="debit_note">Debit Note</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prefix <span class="required">*</span></label>
                        <input type="text" name="prefix" id="s_prefix" class="form-control" placeholder="MB-">
                        <div class="form-text">Prefix will appear at the start of number</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Format <span class="required">*</span></label>
                        <select name="format" id="s_format" class="form-control" required>
                            @foreach($formatOptions as $fmt)
                                <option value="{{ $fmt }}">{{ $fmt }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select numbering format</div>
                    </div>
                </div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Next Number <span class="required">*</span></label>
                        <input type="number" name="next_number" id="s_next_number" class="form-control" placeholder="000513" required>
                        <div class="form-text">Next number to be generated</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reset Frequency <span class="required">*</span></label>
                        <select name="reset_frequency" id="s_reset" class="form-control" required>
                            <option value="yearly">Yearly (Apr)</option>
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                            <option value="never">Never</option>
                        </select>
                        <div class="form-text">How often sequence will reset</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Financial Year <span class="required">*</span></label>
                        <select name="financial_year" id="s_fy" class="form-control">
                            <option value="2025-2026">2025 - 2026 (01 Apr 2025 - 31 Mar 2026)</option>
                            <option value="2024-2025">2024 - 2025 (01 Apr 2024 - 31 Mar 2025)</option>
                        </select>
                        <div class="form-text">Applicable financial year</div>
                    </div>
                </div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="hidden" name="status" value="inactive">
                            <label class="toggle-switch">
                                <input type="checkbox" id="s_status" name="status" value="active" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-size: 13px;">Active</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="s_start_date" class="form-control" value="2025-04-01">
                        <div class="form-text">From which date this series will be active</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" name="description" id="s_description" class="form-control" placeholder="Maintenance bill numbering series">
                        <div class="form-text">Internal reference for this series</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Save Series</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetSeriesForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- About Numbering --}}
    <div class="card">
        <div class="card-body">
            <div class="info-box" style="flex-direction: column; align-items: stretch; gap: 12px;">
                <div style="font-weight: 700; display: flex; align-items: center; gap: 8px;"><i class="fas fa-circle-info"></i> About Numbering</div>
                <div style="display: flex; gap: 8px;"><i class="fas fa-calendar"></i><span><strong>YYYY</strong> – Full year (e.g., 2025)</span></div>
                <div style="display: flex; gap: 8px;"><i class="fas fa-calendar"></i><span><strong>YY</strong> – Short year (e.g., 25)</span></div>
                <div style="display: flex; gap: 8px;"><i class="fas fa-calendar"></i><span><strong>MM</strong> – Month (01 to 12)</span></div>
                <div style="display: flex; gap: 8px;"><i class="fas fa-calendar"></i><span><strong>DD</strong> – Day (01 to 31)</span></div>
                <div style="display: flex; gap: 8px;"><i class="fas fa-hashtag"></i><span><strong>#####</strong> – Sequential number (e.g., 00001)</span></div>
            </div>

            <div style="margin-top: 16px;">
                <div class="form-label" style="margin-bottom: 10px;">Example Formats</div>
                @foreach([['MB-YYYY-#####', 'MB-2025-00001'], ['RCPT-YYMM-#####', 'RCPT-2505-00001'], ['INV-#####', 'INV-00001']] as $ex)
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <span class="prefix-tag">{{ $ex[0] }}</span>
                        <i class="fas fa-arrow-right" style="color: var(--text-muted); font-size: 11px;"></i>
                        <span class="prefix-tag" style="background: var(--gray-100); color: var(--text-primary);">{{ $ex[1] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="note-text" style="margin-top: 12px;">You can reset the sequence manually when required.</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editSeries(data) {
        document.getElementById('seriesForm').action = '{{ url('society/billing/settings/numbering') }}/' + data.id;
        document.getElementById('seriesMethod').value = 'PUT';
        document.getElementById('s_document_type').value = data.document_type ?? 'maintenance_bill';
        document.getElementById('s_prefix').value = data.prefix ?? '';
        document.getElementById('s_format').value = data.format ?? 'YYYY-#####';
        document.getElementById('s_next_number').value = data.next_number ?? '';
        document.getElementById('s_reset').value = data.reset_frequency ?? 'yearly';
        document.getElementById('s_fy').value = data.financial_year ?? '2025-2026';
        document.getElementById('s_status').checked = (data.status ?? 'active') === 'active';
        document.getElementById('s_start_date').value = data.start_date ? data.start_date.substring(0, 10) : '';
        document.getElementById('s_description').value = data.description ?? '';
        document.getElementById('seriesFormCard').scrollIntoView({behavior: 'smooth'});
    }
    function resetSeriesForm() {
        const form = document.getElementById('seriesForm');
        form.reset();
        form.action = '{{ route('society.billing.settings.numbering.store') }}';
        document.getElementById('seriesMethod').value = 'POST';
        document.getElementById('s_status').checked = true;
    }
</script>
@endpush
@endsection
