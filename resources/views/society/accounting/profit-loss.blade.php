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
        <div class="{{ $pl['kpis']['income']['dir'] === 'up' ? 'chg-up' : 'chg-down' }}" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-{{ $pl['kpis']['income']['dir'] === 'up' ? 'up' : 'down' }}"></i> {{ $pl['kpis']['income']['change'] }} vs Last Period</div>
    </div>
    <div class="kpi-tinted expense">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--danger-light); color: var(--danger);"><i class="fas fa-arrow-down"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Total Expenses</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">&#8377; {{ $pl['kpis']['expenses']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="{{ $pl['kpis']['expenses']['dir'] === 'up' ? 'chg-down' : 'chg-up' }}" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-{{ $pl['kpis']['expenses']['dir'] === 'up' ? 'up' : 'down' }}"></i> {{ $pl['kpis']['expenses']['change'] }} vs Last Period</div>
    </div>
    <div class="kpi-tinted profit">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--info-light); color: var(--info);"><i class="fas fa-briefcase"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Net Profit</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">&#8377; {{ $pl['kpis']['net_profit']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="{{ $pl['kpis']['net_profit']['dir'] === 'up' ? 'chg-up' : 'chg-down' }}" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-{{ $pl['kpis']['net_profit']['dir'] === 'up' ? 'up' : 'down' }}"></i> {{ $pl['kpis']['net_profit']['change'] }} vs Last Period</div>
    </div>
    <div class="kpi-tinted margin">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span class="rli-ico" style="width: 38px; height: 38px; border-radius: 50%; background: var(--warning-light); color: var(--warning);"><i class="fas fa-percent"></i></span>
            <span style="font-size: 13px; color: var(--text-secondary);">Profit Margin</span>
        </div>
        <div style="font-size: 22px; font-weight: 700;">{{ $pl['kpis']['margin']['value'] }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">This Period</div>
        <div class="{{ $pl['kpis']['margin']['dir'] === 'up' ? 'chg-up' : 'chg-down' }}" style="font-size: 12px; font-weight: 600; margin-top: 4px;"><i class="fas fa-arrow-{{ $pl['kpis']['margin']['dir'] === 'up' ? 'up' : 'down' }}"></i> {{ $pl['kpis']['margin']['change'] }} vs Last Period</div>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- P&L Summary chart --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Profit &amp; Loss Summary</div></div>
            <div class="card-body">
                <x-chart type="area" :height="220" format="inr" legend label="Income, expense and profit"
                    :labels="$pl['chart']['labels']"
                    :datasets="[
                        ['label' => 'Income', 'data' => $pl['chart']['income'], 'color' => '#10B981'],
                        ['label' => 'Expense', 'data' => $pl['chart']['expense'], 'color' => '#EF4444'],
                        ['label' => 'Net Profit', 'data' => $pl['chart']['profit'], 'color' => '#3B82F6', 'fill' => false, 'dashed' => true],
                    ]" />
                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-top: 16px;">
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
                <x-chart type="doughnut" :size="160" format="inr" :segments="$pl['breakdown']['income']"
                    :center-value="$pl['breakdown']['center_value']" :center-label="$pl['breakdown']['center_label']" />
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
                    <th class="num">This Period<br><span style="font-weight: 400; text-transform: none;">({{ \Illuminate\Support\Carbon::parse($pl['period']['from'])->format('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($pl['period']['to'])->format('d M Y') }}) (&#8377;)</span></th>
                    <th class="num">Previous Period<br><span style="font-weight: 400; text-transform: none;">({{ \Illuminate\Support\Carbon::parse($pl['period']['compare_from'])->format('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($pl['period']['compare_to'])->format('d M Y') }}) (&#8377;)</span></th>
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
