@extends('society.layouts.app')

@section('title', 'Accounting')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Accounting</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Accounting</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.chart-of-accounts') }}" class="btn btn-secondary"><i class="fas fa-sitemap"></i> Chart of Accounts</a>
            <a href="{{ route('society.accounting.opening-balances') }}" class="btn btn-secondary"><i class="fas fa-scale-balanced"></i> Opening Balances</a>
            <a href="{{ route('society.accounting.journal-entries.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Journal Entry</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid stats-grid-5 stats-grid-stacked">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Balance</div>
                    <div class="stat-value">&#8377; {{ $stats['total_balance'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>All Accounts</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Income</div>
                    <div class="stat-value">&#8377; {{ $stats['total_income'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-value">&#8377; {{ $stats['total_expenses'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-user"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Receivables</div>
                    <div class="stat-value">&#8377; {{ $stats['total_receivables'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Pending Dues</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-credit-card"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Payables</div>
                    <div class="stat-value">&#8377; {{ $stats['total_payables'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Pending Bills</span></div>
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
                <form method="GET" action="{{ route('society.accounting.index') }}">
                    <div style="display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr auto auto; gap: 14px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Date Range</label>
                            <input type="text" class="form-control" value="01 May 2025 - 30 May 2025" readonly>
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
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Payment Mode</label>
                            <select name="mode" class="form-control">
                                <option value="">All Payment Modes</option>
                                @foreach($paymentModes as $mode)
                                    <option value="{{ $mode }}">{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-control">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}">{{ $location }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Charts --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Cash Flow Overview</div>
                    <a href="{{ route('society.accounting.transactions') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View Report</a>
                </div>
                <div class="card-body">
                    @include('society.partials.area-chart', [
                        'series' => [
                            ['points' => $cashFlow['income'], 'stroke' => '#10B981', 'fill' => 'rgba(16,185,129,0.10)'],
                            ['points' => $cashFlow['expense'], 'stroke' => '#EF4444', 'fill' => 'rgba(239,68,68,0.08)'],
                        ],
                        'labels' => $cashFlow['labels'],
                        'max' => $cashFlow['max'],
                        'yTicks' => ['₹2.5L' => 250000, '₹2L' => 200000, '₹1L' => 100000, '₹0' => 0],
                    ])
                    <div class="chart-legend" style="justify-content: center; gap: 24px; margin-top: 8px;">
                        <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Income</span></div>
                        <div class="legend-item"><span class="legend-dot" style="background: #EF4444;"></span><span class="legend-label">Expense</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Account Balance Summary</div>
                    <a href="{{ route('society.accounting.chart-of-accounts') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All Accounts</a>
                </div>
                <div class="card-body">
                    @include('society.partials.donut', [
                        'segments' => collect($balanceSummary['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                        'centerValue' => $balanceSummary['center_value'],
                        'centerLabel' => $balanceSummary['center_label'],
                        'size' => 170,
                        'stroke' => 14,
                    ])
                    <div class="chart-legend" style="margin-top: 20px;">
                        @foreach($balanceSummary['segments'] as $seg)
                            <div class="legend-item">
                                <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                                <span class="legend-label">{{ $seg['label'] }}</span>
                                <span class="legend-value">{{ $seg['amount'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Transactions</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reference No.</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th class="num" style="text-align: right;">Debit (&#8377;)</th>
                                <th class="num" style="text-align: right;">Credit (&#8377;)</th>
                                <th class="num" style="text-align: right;">Balance (&#8377;)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent as $txn)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $txn->date?->format('d M Y') }}</td>
                                    <td><span class="badge {{ $txn->typeBadgeClass() }}">{{ $txn->typeLabel() }}</span></td>
                                    <td style="font-weight: 600; white-space: nowrap;">{{ $txn->reference_no }}</td>
                                    <td>{{ $txn->description }}</td>
                                    <td>{{ $txn->account?->name ?? '—' }}</td>
                                    <td style="text-align: right;">{{ (float) $txn->debit > 0 ? number_format((float) $txn->debit, 2) : '—' }}</td>
                                    <td style="text-align: right;">{{ (float) $txn->credit > 0 ? number_format((float) $txn->credit, 2) : '—' }}</td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $txn->running_balance, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer" style="padding: 12px 20px;">
                <a href="{{ route('society.accounting.transactions') }}" class="btn btn-outline-primary" style="width: 100%;">View All Transactions <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
            </div>
        </div>

        <div class="tip-banner orange"><i class="fas fa-lightbulb"></i><span>Keep your accounting up to date to get accurate reports and insights.</span></div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Bank Accounts --}}
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Bank Accounts</div>
                    <a href="{{ route('society.accounting.chart-of-accounts') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All</a>
                </div>
                @foreach($bankAccounts as $bank)
                    @php [$bg, $fg] = \App\Models\AssetCategory::tint($bank['color']); @endphp
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $bank['icon'] }}"></i></span>
                        <div class="rli-main">
                            <div class="rli-title">{{ $bank['name'] }}</div>
                            <div class="rli-sub">{{ $bank['sub'] }}</div>
                        </div>
                        <span class="rli-meta">&#8377; {{ $bank['amount'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Payables & Receivables --}}
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Payables &amp; Receivables</div>
                    <a href="{{ route('society.accounting.transactions') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All</a>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <div style="font-size: 13px; font-weight: 600;">Total Receivables</div>
                        <div style="font-size: 11px; color: var(--text-muted);">From 48 Members</div>
                    </div>
                    <div style="font-weight: 700; color: var(--orange);">&#8377; {{ $stats['total_receivables'] }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600;">Total Payables</div>
                        <div style="font-size: 11px; color: var(--text-muted);">To 12 Vendors</div>
                    </div>
                    <div style="font-weight: 700; color: var(--info);">&#8377; {{ $stats['total_payables'] }}</div>
                </div>
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-receipt', 'label' => 'Add Receipt', 'desc' => 'Record a new receipt', 'url' => route('society.accounting.receipts.create'), 'color' => 'green'],
            ['icon' => 'fa-credit-card', 'label' => 'Add Payment', 'desc' => 'Record a new payment', 'url' => route('society.accounting.payments.create'), 'color' => 'red'],
            ['icon' => 'fa-book', 'label' => 'Add Journal Entry', 'desc' => 'Post a journal entry', 'url' => route('society.accounting.journal-entries.create'), 'color' => 'purple'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Bank Reconciliation', 'desc' => 'Match bank statement', 'url' => route('society.accounting.bank-reconciliation'), 'color' => 'blue'],
        ]])
    </div>
</div>
@endsection
