@extends('society.layouts.app')

@section('title', 'Balance Sheet')

@section('content')
@php
    $asOnLabel = \Illuminate\Support\Carbon::parse($bs['as_on'])->format('d M Y');
    $compareOnLabel = \Illuminate\Support\Carbon::parse($bs['compare_on'])->format('d M Y');
@endphp
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Balance Sheet</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Balance Sheet</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.balance-sheet') }}" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-print"></i> Print</a>
            <a href="{{ route('society.accounting.balance-sheet') }}" class="btn btn-primary"><i class="fas fa-gear"></i> Customize Report</a>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="card">
    <div class="card-body" style="padding-bottom: 0;">
        @include('society.accounting._tabs', ['active' => $active])
    </div>
</div>

{{-- Filter bar --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.accounting.balance-sheet') }}">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 14px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">As On Date *</label>
                    <input type="date" name="as_on" class="form-control" value="{{ $asOn }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Compare With</label>
                    <input type="date" name="compare_on" class="form-control" value="{{ request('compare_on', $bs['compare_on']) }}">
                </div>
                <button type="submit" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
        @unless($bs['balanced'])
            <div class="alert alert-danger" style="margin-top: 12px;"><i class="fas fa-exclamation-circle"></i> Assets do not equal liabilities + equity — check for unposted opening balances.</div>
        @endunless
    </div>
</div>

{{-- Stat cards --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-building-columns"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Assets</div>
            <div class="stat-value">&#8377; {{ $bs['stats']['total_assets'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>As on {{ $asOnLabel }}</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hand-holding-dollar"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Liabilities</div>
            <div class="stat-value">&#8377; {{ $bs['stats']['total_liabilities'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>As on {{ $asOnLabel }}</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-scale-balanced"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Equity</div>
            <div class="stat-value">&#8377; {{ $bs['stats']['total_equity'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>As on {{ $asOnLabel }}</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-credit-card"></i></div>
        <div class="stat-info">
            <div class="stat-label">Balance Sheet Total</div>
            <div class="stat-value">&#8377; {{ $bs['stats']['bs_total'] }}</div>
            <div class="stat-trend" style="color: var(--text-muted);"><span>(Assets = Liabilities + Equity)</span></div>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Two-pane balance sheet --}}
        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="bs-panes">
                    <div class="bs-pane">
                        <div class="bs-head-assets"><span>ASSETS</span><span class="bs-head-note">Amounts in &#8377;</span></div>
                        <div class="table-responsive">
                        <table class="fin-table bs-table">
                            <colgroup><col><col class="bs-col-amount"><col class="bs-col-amount"></colgroup>
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th class="num">{{ $asOnLabel }}</th>
                                    <th class="num">{{ $compareOnLabel }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bs['assets'] as $section)
                                    <tr class="fin-section-row income"><td colspan="3">{{ $section['group'] }}</td></tr>
                                    @forelse($section['rows'] as $row)
                                        <tr>
                                            <td class="bs-item">{{ $row[0] }}</td>
                                            <td class="num">{{ $row[1] }}</td>
                                            <td class="num">{{ $row[2] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="bs-item bs-empty">No balances</td></tr>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        <div class="table-responsive bs-pane-footer">
                        <table class="fin-table bs-table">
                            <colgroup><col><col class="bs-col-amount"><col class="bs-col-amount"></colgroup>
                            <tbody>
                                <tr class="fin-total-row income">
                                    <td>TOTAL ASSETS</td>
                                    <td class="num">{{ $bs['total_assets_row'][0] }}</td>
                                    <td class="num">{{ $bs['total_assets_row'][1] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <div class="bs-pane">
                        <div class="bs-head-liab"><span>LIABILITIES &amp; EQUITY</span><span class="bs-head-note">Amounts in &#8377;</span></div>
                        <div class="table-responsive">
                        <table class="fin-table bs-table">
                            <colgroup><col><col class="bs-col-amount"><col class="bs-col-amount"></colgroup>
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th class="num">{{ $asOnLabel }}</th>
                                    <th class="num">{{ $compareOnLabel }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bs['liabilities'] as $section)
                                    <tr class="fin-section-row {{ $section['color'] === 'red' ? 'expense' : 'income' }}"><td colspan="3" style="{{ $section['color'] === 'orange' ? 'color: var(--orange);' : '' }}">{{ $section['group'] }}</td></tr>
                                    @forelse($section['rows'] as $row)
                                        <tr>
                                            <td class="bs-item">{{ $row[0] }}</td>
                                            <td class="num">{{ $row[1] }}</td>
                                            <td class="num">{{ $row[2] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="bs-item bs-empty">No balances</td></tr>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        <div class="table-responsive bs-pane-footer">
                        <table class="fin-table bs-table">
                            <colgroup><col><col class="bs-col-amount"><col class="bs-col-amount"></colgroup>
                            <tbody>
                                <tr class="fin-total-row expense">
                                    <td>TOTAL LIABILITIES &amp; EQUITY</td>
                                    <td class="num">{{ $bs['total_liab_row'][0] }}</td>
                                    <td class="num">{{ $bs['total_liab_row'][1] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tip-banner blue"><i class="fas fa-circle-info"></i><span>Balance Sheet shows the financial position of the society as on {{ $asOnLabel }}, compared with {{ $compareOnLabel }}.</span></div>
    </div>

    {{-- Right rail --}}
    <div>
        <div class="card">
            <div class="card-header"><div class="card-title">About this Report</div></div>
            <div class="card-body">
                <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">The Balance Sheet presents the society's assets, liabilities, and equity as on the selected date, giving a snapshot of its financial position. Compare with a previous date to track growth.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Key Insights</div></div>
            <div class="card-body">
                @foreach($bs['insights'] as $insight)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 13px;">
                        <span style="color: var(--text-secondary);">{{ $insight['label'] }}</span>
                        <span class="{{ ['up' => 'chg-up', 'down' => 'chg-down'][$insight['dir']] ?? '' }}" style="font-weight: 600; white-space: nowrap;{{ $insight['dir'] === 'flat' ? ' color: var(--text-muted);' : '' }}"><i class="fas fa-{{ ['up' => 'arrow-up', 'down' => 'arrow-down'][$insight['dir']] ?? 'minus' }}"></i> {{ $insight['change'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-sitemap', 'label' => 'View Chart of Accounts', 'desc' => 'Browse all accounts', 'url' => route('society.accounting.chart-of-accounts'), 'color' => 'blue'],
            ['icon' => 'fa-scale-balanced', 'label' => 'View Trial Balance', 'desc' => 'Debit / credit summary', 'url' => route('society.accounting.trial-balance'), 'color' => 'green'],
            ['icon' => 'fa-chart-line', 'label' => 'Profit & Loss Statement', 'desc' => 'Income vs expenses', 'url' => route('society.accounting.profit-loss'), 'color' => 'purple'],
            ['icon' => 'fa-money-bill-transfer', 'label' => 'Cash Flow Statement', 'desc' => 'Inflows and outflows', 'url' => route('society.accounting.index'), 'color' => 'orange'],
        ]])
    </div>
</div>
@endsection
