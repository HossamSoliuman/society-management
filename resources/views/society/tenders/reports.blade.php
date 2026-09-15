@extends('society.layouts.app')

@section('title', 'Tender Reports')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Tender Reports</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenders.active') }}">Tenders</a>
                <span class="breadcrumb-separator">/</span>
                <span>Reports</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.tenders.reports') }}" style="display: flex; gap: 12px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
            <div class="form-group" style="margin-bottom: 0;"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Apply</button>
        </form>
    </div>
</div>

@include('society.tenders._stats', ['stats' => [
    ['label' => 'Tenders Created', 'value' => (string) $total, 'sub' => 'In selected period', 'icon' => 'fa-clipboard-list', 'color' => 'blue'],
    ['label' => 'Awarded', 'value' => (string) (int) ($byStatus['awarded']->c ?? 0), 'sub' => 'Vendor selected', 'icon' => 'fa-trophy', 'color' => 'green'],
    ['label' => 'Estimated Value', 'value' => '&#8377; '.number_format($estimated), 'sub' => 'Sum of estimates', 'icon' => 'fa-calculator', 'color' => 'orange'],
    ['label' => 'Contracted Value', 'value' => '&#8377; '.number_format($contracted), 'sub' => 'Awarded + closed', 'icon' => 'fa-file-signature', 'color' => 'purple'],
]])

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">By Department</div>
                <table class="data-table">
                    <thead><tr><th>Department</th><th style="text-align: right;">Tenders</th><th style="text-align: right;">Contract Value</th></tr></thead>
                    <tbody>
                        @forelse($byDepartment as $row)
                            <tr><td>{{ $row->department ?? '—' }}</td><td style="text-align: right;">{{ $row->c }}</td><td style="text-align: right;">{{ number_format((float) $row->contract, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">No tenders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">By Vendor</div>
                <table class="data-table">
                    <thead><tr><th>Vendor</th><th style="text-align: right;">Awards</th><th style="text-align: right;">Contract Value</th></tr></thead>
                    <tbody>
                        @forelse($byVendor as $row)
                            <tr><td>{{ $row->awarded_vendor }}</td><td style="text-align: right;">{{ $row->c }}</td><td style="text-align: right;">{{ number_format((float) $row->contract, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">Nothing awarded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Recent Tenders</div>
                <table class="data-table">
                    <thead><tr><th>Reference</th><th>Title</th><th>Status</th><th style="text-align: right;">Value</th></tr></thead>
                    <tbody>
                        @foreach($recent as $tender)
                            <tr>
                                <td><a href="{{ route('society.tenders.show', $tender) }}">{{ $tender->reference_no }}</a></td>
                                <td>{{ $tender->title }}</td>
                                <td><span class="badge {{ $tender->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $tender->status)) }}</span></td>
                                <td style="text-align: right;">{{ number_format((float) ($tender->contract_value ?? $tender->estimated_value ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">By Status</div>
                @foreach(['draft' => 'Draft', 'under_review' => 'Under Review', 'open' => 'Open', 'in_progress' => 'In Progress', 'awarded' => 'Awarded', 'closed' => 'Closed'] as $status => $label)
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-color); font-size: 13px;">
                        <span style="color: var(--text-secondary);">{{ $label }}</span>
                        <span style="font-weight: 600;">{{ (int) ($byStatus[$status]->c ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
