@extends('society.layouts.app')

@section('title', 'Profit & Loss')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Profit &amp; Loss</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Profit &amp; Loss</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.profit-loss') }}" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-print"></i> Print</a>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="card">
    <div class="card-body" style="padding-bottom: 0;">
        @include('society.accounting._tabs', ['active' => $active])
    </div>
</div>

{{-- Period filter --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.accounting.profit-loss') }}">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 14px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">From *</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">To *</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Compare From</label>
                    <input type="date" name="compare_from" class="form-control" value="{{ request('compare_from', $pl['period']['compare_from']) }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Compare To</label>
                    <input type="date" name="compare_to" class="form-control" value="{{ request('compare_to', $pl['period']['compare_to']) }}">
                </div>
                <button type="submit" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-filter"></i> Apply</button>
            </div>
        </form>
    </div>
</div>

{{-- KPI cards --}}
<div class="stats-grid stats-grid-4">
    <div class="kpi-tinted income">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--success-light); color: var(--success);"><i class="fas fa-arrow-up"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Total Income</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">&#8377; {{ $pl['kpis']['income']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="chg-up" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-up"></i> {{ $pl['kpis']['income']['change'] }} vs Last Period</div>
    </div>
    <div class="kpi-tinted expense">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--danger-light); color: var(--danger);"><i class="fas fa-arrow-down"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Total Expenses</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">&#8377; {{ $pl['kpis']['expenses']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="chg-down" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-down"></i> {{ $pl['kpis']['expenses']['change'] }} vs Last Period</div>
    </div>
    <div class="kpi-tinted profit">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--info-light); color: var(--info);"><i class="fas fa-briefcase"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Net Profit</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">&#8377; {{ $pl['kpis']['net_profit']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="chg-up" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-up"></i> {{ $pl['kpis']['net_profit']['change'] }} vs Last Period</div>
    </div>
    <div class="kpi-tinted margin">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--warning-light); color: var(--warning);"><i class="fas fa-percent"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Profit Margin</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">{{ $pl['kpis']['margin']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="chg-up" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-up"></i> {{ $pl['kpis']['margin']['change'] }} vs Last Period</div>
    </div>
</div>

{{-- Filter bar --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.accounting.profit-loss') }}">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto auto; gap: 14px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" value="{{ request('from', '2025-05-01') }}" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" value="{{ request('to', '2025-05-30') }}" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Compare With</label>
                    <select name="compare" class="form-control">
                        <option>Previous Period</option>
                        <option>Same Period Last Year</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Account</label>
                    <select name="account" class="form-control">
                        <option value="">All Accounts</option>
                        @foreach($accountsForFilter as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('society.accounting.profit-loss') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- P&L Summary chart --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Profit &amp; Loss Summary</div></div>
            <div class="card-body">
                @include('society.partials.area-chart', [
                    'series' => [
                        ['points' => $pl['chart']['income'], 'stroke' => '#10B981', 'fill' => 'rgba(16,185,129,0.10)'],
                        ['points' => $pl['chart']['expense'], 'stroke' => '#EF4444', 'fill' => 'rgba(239,68,68,0.08)'],
                        ['points' => $pl['chart']['profit'], 'stroke' => '#3B82F6', 'fill' => null],
                    ],
                    'labels' => $pl['chart']['labels'],
                    'max' => $pl['chart']['max'],
                    'yTicks' => ['₹10L' => 1000000, '₹5L' => 500000, '₹0' => 0],
                ])
                <div class="chart-legend" style="justify-content: center; gap: 24px; margin: 8px 0 16px;">
                    <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Income</span></div>
                    <div class="legend-item"><span class="legend-dot" style="background: #EF4444;"></span><span class="legend-label">Expenses</span></div>
                    <div class="legend-item"><span class="legend-dot" style="background: #3B82F6;"></span><span class="legend-label">Profit</span></div>
                </div>
                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px;">
                    <div style="display: flex; justify-content: space-between; padding: 6px 0;"><span style="color: var(--text-secondary);">Total Income</span><span style="font-weight: 600;">&#8377; {{ $pl['summary']['total_income'] }}</span></div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0;"><span style="color: var(--text-secondary);">Total Expenses</span><span style="font-weight: 600;">&#8377; {{ $pl['summary']['total_expenses'] }}</span></div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-top: 1px solid var(--border-color); margin-top: 4px;"><span style="font-weight: 700;">Net Profit</span><span style="font-weight: 700; color: var(--success);">&#8377; {{ $pl['summary']['net_profit'] }}</span></div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0;"><span style="font-weight: 700;">Profit Margin</span><span style="font-weight: 700;">{{ $pl['summary']['margin'] }}</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Income &amp; Expense Breakdown</div>
            </div>
            <div class="card-body">
                @include('society.partials.donut', [
                    'segments' => collect($pl['breakdown']['income'])->map(fn ($s) => ['value' => (float) str_replace(',', '', $s['amount']), 'color' => $s['color']])->all(),
                    'centerValue' => $pl['breakdown']['center_value'],
                    'centerLabel' => $pl['breakdown']['center_label'],
                    'size' => 160,
                    'stroke' => 14,
                ])
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin: 16px 0 6px;">Income</div>
                @foreach($pl['breakdown']['income'] as $row)
                    <div style="display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: 13px;">
                        <span class="legend-dot" style="background: {{ $row['color'] }};"></span>
                        <span style="flex: 1; color: var(--text-secondary);">{{ $row['label'] }}</span>
                        <span style="font-weight: 600;">{{ $row['amount'] }}</span>
                    </div>
                @endforeach
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin: 16px 0 6px;">Expenses</div>
                @foreach($pl['breakdown']['expenses'] as $row)
                    <div style="display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: 13px;">
                        <span class="legend-dot" style="background: {{ $row['color'] }};"></span>
                        <span style="flex: 1; color: var(--text-secondary);">{{ $row['label'] }}</span>
                        <span style="font-weight: 600;">{{ $row['amount'] }}</span>
                    </div>
                @endforeach
                <a href="{{ route('society.accounting.chart-of-accounts') }}" class="btn btn-outline-primary btn-sm" style="width: 100%; margin-top: 12px;">View All Accounts</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Key Insights</div></div>
            <div class="card-body">
                @foreach($pl['insights'] as $insight)
                    @php [$bg, $fg] = \App\Models\AssetCategory::tint($insight['color']); @endphp
                    <div style="display: flex; gap: 10px; padding: 8px 0;">
                        <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $insight['icon'] }}"></i></span>
                        <span style="font-size: 13px; color: var(--text-secondary);">{{ $insight['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- P&L Statement --}}
<div class="card">
    <div class="card-header"><div class="card-title">Profit &amp; Loss Statement</div></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
        <table class="fin-table">
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th class="num">This Period<br><span style="font-weight: 400; text-transform: none;">(01 May - 30 May 2025) (&#8377;)</span></th>
                    <th class="num">Previous Period<br><span style="font-weight: 400; text-transform: none;">(01 Apr - 30 Apr 2025) (&#8377;)</span></th>
                    <th class="num">Change (&#8377;)</th>
                    <th class="num">Change (%)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="fin-section-row income"><td colspan="5"><i class="fas fa-caret-down"></i> Income</td></tr>
                @foreach($pl['income_rows'] as $row)
                    <tr>
                        <td style="padding-left: 32px;">{{ $row[0] }}</td>
                        <td class="num">{{ $row[1] }}</td>
                        <td class="num">{{ $row[2] }}</td>
                        <td class="num">{{ $row[3] }}</td>
                        <td class="num {{ $row[5] === 'up' ? 'chg-up' : 'chg-down' }}"><i class="fas fa-arrow-{{ $row[5] === 'up' ? 'up' : 'down' }}"></i> {{ $row[4] }}</td>
                    </tr>
                @endforeach
                <tr class="fin-total-row income">
                    <td>Total Income</td>
                    <td class="num">{{ $pl['total_income'][0] }}</td>
                    <td class="num">{{ $pl['total_income'][1] }}</td>
                    <td class="num">{{ $pl['total_income'][2] }}</td>
                    <td class="num">{{ $pl['total_income'][3] }}</td>
                </tr>

                <tr class="fin-section-row expense"><td colspan="5"><i class="fas fa-caret-down"></i> Expenses</td></tr>
                @foreach($pl['expense_rows'] as $row)
                    <tr>
                        <td style="padding-left: 32px;">{{ $row[0] }}</td>
                        <td class="num">{{ $row[1] }}</td>
                        <td class="num">{{ $row[2] }}</td>
                        <td class="num">{{ $row[3] }}</td>
                        <td class="num {{ $row[5] === 'up' ? 'chg-up' : 'chg-down' }}"><i class="fas fa-arrow-{{ $row[5] === 'up' ? 'up' : 'down' }}"></i> {{ $row[4] }}</td>
                    </tr>
                @endforeach
                <tr class="fin-total-row expense">
                    <td>Total Expenses</td>
                    <td class="num">{{ $pl['total_expenses'][0] }}</td>
                    <td class="num">{{ $pl['total_expenses'][1] }}</td>
                    <td class="num">{{ $pl['total_expenses'][2] }}</td>
                    <td class="num">{{ $pl['total_expenses'][3] }}</td>
                </tr>

                <tr class="fin-total-row net">
                    <td>Net Profit</td>
                    <td class="num">{{ $pl['net_profit_row'][0] }}</td>
                    <td class="num">{{ $pl['net_profit_row'][1] }}</td>
                    <td class="num">{{ $pl['net_profit_row'][2] }}</td>
                    <td class="num">{{ $pl['net_profit_row'][3] }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="tip-banner blue"><i class="fas fa-lightbulb"></i><span>Tip: Use filters to analyze specific accounts and periods.</span></div>
@endsection
