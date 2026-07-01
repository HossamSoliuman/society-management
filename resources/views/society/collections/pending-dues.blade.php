@extends('society.layouts.app')

@section('title', 'Pending Dues')

@php
    $inr = function ($n) {
        $n = (string) (int) $n;
        $last3 = substr($n, -3);
        $rest = substr($n, 0, -3);
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $last3 = ','.$last3;
        }
        return $rest.$last3;
    };
    $agingTabs = [
        'all' => 'All Dues ('.$counts['all'].')',
        '0-30' => '0 - 30 Days ('.$counts['0-30'].')',
        '31-60' => '31 - 60 Days ('.$counts['31-60'].')',
        '61-90' => '61 - 90 Days ('.$counts['61-90'].')',
        '90+' => '90+ Days ('.$counts['90+'].')',
    ];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Pending Dues</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Collections</a>
                <span class="breadcrumb-separator">/</span>
                <span>Pending Dues</span>
            </div>
        </div>
        <a href="#" class="btn btn-outline-secondary"><i class="fas fa-download"></i> Export Report</a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- KPI cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            @include('society.partials.stat-card', ['icon' => 'fa-file-invoice', 'iconVariant' => 'danger', 'label' => 'Total Outstanding', 'value' => '&#8377; '.$inr($kpis['outstanding']), 'trend' => $kpis['outstanding_sub'], 'trendType' => 'danger'])
            @include('society.partials.stat-card', ['icon' => 'fa-clock', 'iconVariant' => 'warning', 'label' => 'Due This Month', 'value' => '&#8377; '.$inr($kpis['due_month']), 'trend' => $kpis['due_month_sub'], 'trendType' => 'warning'])
            @include('society.partials.stat-card', ['icon' => 'fa-calendar-xmark', 'iconVariant' => 'danger', 'label' => 'Overdue', 'value' => '&#8377; '.$inr($kpis['overdue']), 'trend' => $kpis['overdue_sub'], 'trendType' => 'danger'])
            @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Avg. Days Overdue', 'value' => $kpis['avg_days'], 'trend' => $kpis['avg_days_sub'], 'trendType' => 'muted'])
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.collections.pending-dues') }}">
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Building</label><select class="form-control"><option>All Buildings</option></select></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Wing / Block</label><select class="form-control"><option>All Wings</option></select></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Floor</label><select class="form-control"><option>All Floors</option></select></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Unit Type</label><select class="form-control"><option>All Types</option></select></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Status</label><select class="form-control"><option>All Status</option></select></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 2fr auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Due Date</label><input type="text" class="form-control" placeholder="Select date range"></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Dues Aging</label><select class="form-control"><option>All</option></select></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Member Type</label><select class="form-control"><option>All</option></select></div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Search</label>
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by member name, flat no, mobile…">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.collections.pending-dues') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Aging tabs + table --}}
        <div class="card">
            <div class="card-body">
                <div class="tab-pills">
                    @foreach($agingTabs as $key => $label)
                        <a href="{{ route('society.collections.pending-dues', ['bucket' => $key]) }}" class="tab-pill {{ $bucket === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="padding-left: 20px; width: 36px;"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked)"></th>
                                <th>Member / Unit</th>
                                <th>Bill Period</th>
                                <th>Due Date</th>
                                <th style="text-align: right;">Total Due (&#8377;)</th>
                                <th style="text-align: right;">Paid (&#8377;)</th>
                                <th style="text-align: right;">Balance (&#8377;)</th>
                                <th>Days Overdue</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td style="padding-left: 20px;"><input type="checkbox" class="row-check"></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="avatar" style="width: 36px; height: 36px;">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($row['member_name']) }}&background=E84B1E&color=fff" alt="">
                                            </div>
                                            <div>
                                                <div style="font-weight: 600;">{{ $row['member_name'] }}</div>
                                                <div class="cell-sub">{{ $row['flat_number'] }}, {{ $row['wing'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $row['bill_period'] }}</td>
                                    <td>{{ $row['due_date'] }}</td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format($row['total_due'], 2) }}</td>
                                    <td style="text-align: right; font-weight: 600; color: {{ $row['paid'] > 0 ? 'var(--success)' : 'var(--text-muted)' }};">{{ number_format($row['paid'], 2) }}</td>
                                    <td style="text-align: right; font-weight: 700; color: var(--danger);">{{ number_format($row['balance'], 2) }}</td>
                                    <td style="{{ $row['days_overdue'] > 0 ? 'color: var(--danger); font-weight: 700;' : 'color: var(--text-muted);' }}">{{ $row['days_overdue'] }}</td>
                                    <td>
                                        @if($row['days_overdue'] === 0)
                                            <span class="status-badge due-today">Due Today</span>
                                        @else
                                            <span class="status-badge overdue">{{ $row['days_overdue'] }} Days Overdue</span>
                                        @endif
                                    </td>
                                    <td><a href="#" class="action-btn view" title="View"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-circle-check"></i></div>
                                            <div class="empty-state-title">No pending dues</div>
                                            <div class="empty-state-text">No dues match the selected aging bucket.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper">
                    <div class="pagination-info">Showing 1 to {{ $rows->count() }} of {{ $counts['all'] }} entries</div>
                    <div class="pagination">
                        <span class="page-link disabled"><i class="fas fa-angles-left"></i></span>
                        <span class="page-link active">1</span>
                        <a href="#" class="page-link">2</a>
                        <a href="#" class="page-link">3</a>
                        <span class="page-link disabled">&hellip;</span>
                        <a href="#" class="page-link"><i class="fas fa-angles-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Dues Aging Summary --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Dues Aging Summary</div>
                @include('society.partials.donut', [
                    'segments' => collect($aging['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                    'centerValue' => $aging['center_value'],
                    'centerLabel' => $aging['center_label'],
                    'size' => 170,
                    'stroke' => 14,
                ])
                <div class="chart-legend" style="margin-top: 20px;">
                    @foreach($aging['segments'] as $seg)
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
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Quick Actions</div>
                <div class="quick-link-list">
                    <a href="{{ route('society.collections.create') }}" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-money-bill-wave"></i></span>
                        <span><span class="ql-title">Record Payment</span><br><span class="ql-sub">Add new payment</span></span>
                    </a>
                    <a href="#" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-bell"></i></span>
                        <span><span class="ql-title">Send Reminder</span><br><span class="ql-sub">Send payment reminders</span></span>
                    </a>
                    <a href="#" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-file-arrow-down"></i></span>
                        <span><span class="ql-title">Dues Report</span><br><span class="ql-sub">Download dues report</span></span>
                    </a>
                    <a href="#" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-list-check"></i></span>
                        <span><span class="ql-title">Follow Up List</span><br><span class="ql-sub">View follow up list</span></span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Note --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;"><i class="fas fa-circle-info"></i> Note</div>
                <div class="note-text" style="font-style: normal;">Dues are calculated based on unpaid maintenance bills. Keep your records updated for accurate collection tracking.</div>
            </div>
        </div>
    </div>
</div>
@endsection
