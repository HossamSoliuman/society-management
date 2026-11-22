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
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- KPI cards --}}
        <div class="stats-grid stats-grid-4">
            @include('society.partials.stat-card', ['icon' => 'fa-file-invoice', 'iconVariant' => 'danger', 'label' => 'Total Outstanding', 'value' => '₹ '.$inr($kpis['outstanding']), 'trend' => $kpis['outstanding_sub'], 'trendType' => 'danger'])
            @include('society.partials.stat-card', ['icon' => 'fa-clock', 'iconVariant' => 'warning', 'label' => 'Due This Month', 'value' => '₹ '.$inr($kpis['due_month']), 'trend' => $kpis['due_month_sub'], 'trendType' => 'warning'])
            @include('society.partials.stat-card', ['icon' => 'fa-calendar-xmark', 'iconVariant' => 'danger', 'label' => 'Overdue', 'value' => '₹ '.$inr($kpis['overdue']), 'trend' => $kpis['overdue_sub'], 'trendType' => 'danger'])
            @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Avg. Days Overdue', 'value' => $kpis['avg_days'], 'trend' => $kpis['avg_days_sub'], 'trendType' => 'muted'])
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.collections.pending-dues') }}">
                    <input type="hidden" name="bucket" value="{{ $bucket }}">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Building / Wing</label>
                            <select name="tower" class="form-control">
                                <option value="">All Buildings</option>
                                @foreach($towers as $tower)
                                    <option value="{{ $tower }}" @selected(request('tower') === $tower)>{{ $tower }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                @foreach(['pending' => 'Pending', 'partial' => 'Partially Paid', 'overdue' => 'Overdue'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Due From</label><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
                        <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Due To</label><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 16px; align-items: end;">
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
                        <a href="{{ route('society.collections.pending-dues', array_merge(request()->except(['bucket', 'page']), ['bucket' => $key])) }}" class="tab-pill {{ $bucket === $key ? 'active' : '' }}">{{ $label }}</a>
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
                                    <td style="{{ $row['days_overdue'] > 0 ? 'color: var(--danger); font-weight: 700;' : 'color: var(--text-muted);' }}">{{ max(0, $row['days_overdue']) }}</td>
                                    <td>
                                        @if($row['days_overdue'] < 0)
                                            <span class="status-badge pending">Due in {{ abs($row['days_overdue']) }} {{ \Illuminate\Support\Str::plural('day', abs($row['days_overdue'])) }}</span>
                                        @elseif($row['days_overdue'] === 0)
                                            <span class="status-badge due-today">Due Today</span>
                                        @else
                                            <span class="status-badge overdue">{{ $row['days_overdue'] }} Days Overdue</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('society.billing.bills.show', $row['id']) }}" class="action-btn view" title="View bill"><i class="fas fa-eye"></i></a></td>
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

                @include('society.partials.pagination', ['items' => $rows, 'firstLast' => false, 'side' => 2, 'unit' => 'dues'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Dues Aging Summary --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Dues Aging Summary</div>
                <x-chart type="doughnut" :size="170" format="inr" :segments="$aging['segments']"
                    :center-value="$aging['center_value']" :center-label="$aging['center_label']" />
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
                    <a href="{{ route('society.billing.settings.notifications') }}" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-bell"></i></span>
                        <span><span class="ql-title">Reminder Settings</span><br><span class="ql-sub">When dues reminders go out</span></span>
                    </a>
                    <a href="{{ route('society.reports.show', 'defaulter') }}" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-file-arrow-down"></i></span>
                        <span><span class="ql-title">Defaulter Report</span><br><span class="ql-sub">Excel or PDF of all dues</span></span>
                    </a>
                    <a href="{{ route('society.collections.pending-dues', ['bucket' => '90+']) }}" class="quick-link-item">
                        <span class="ql-icon"><i class="fas fa-list-check"></i></span>
                        <span><span class="ql-title">Follow Up List</span><br><span class="ql-sub">Dues older than 90 days</span></span>
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
