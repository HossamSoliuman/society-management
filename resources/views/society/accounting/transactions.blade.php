@extends('society.layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Transactions</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Transactions</span>
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
        <div class="stats-grid stats-grid-5">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-wallet"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Receipts</div>
                    <div class="stat-value">&#8377; {{ $stats['receipts'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Payments</div>
                    <div class="stat-value">&#8377; {{ $stats['payments'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-sitemap"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Journal Entries</div>
                    <div class="stat-value">{{ $stats['journals'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-chart-pie"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Net Balance</div>
                    <div class="stat-value">&#8377; {{ $stats['net_balance'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
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
                <form method="GET" action="{{ route('society.accounting.transactions') }}">
                    <div style="display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr 1fr auto auto; gap: 12px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Date Range</label>
                            <input type="text" class="form-control" value="01 May 2025 - 30 May 2025" readonly>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Account</label>
                            <select name="account" class="form-control">
                                <option value="">All Accounts</option>
                                @foreach($accountsForFilter as $account)
                                    <option value="{{ $account->id }}" {{ request('account') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>Receipt</option>
                                <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>Payment</option>
                                <option value="journal" {{ request('type') === 'journal' ? 'selected' : '' }}>Journal</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Payment Mode</label>
                            <select name="mode" class="form-control">
                                <option value="">All Modes</option>
                                @foreach($paymentModes as $mode)
                                    <option value="{{ $mode }}" {{ request('mode') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-control">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" {{ request('location') === $location ? 'selected' : '' }}>{{ $location }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.accounting.transactions') }}" class="btn btn-secondary"><i class="fas fa-file-export"></i> Export</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sub-tabs + table --}}
        <div class="card">
            <div class="card-body">
                <div class="tabs">
                    @foreach(['all' => 'All Transactions', 'receipts' => 'Receipts', 'payments' => 'Payments', 'journal-entries' => 'Journal Entries'] as $key => $label)
                        <a href="{{ route('society.accounting.transactions', ['tab' => $key]) }}" class="tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reference No.</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Payment Mode</th>
                                <th class="num" style="text-align: right;">Debit (&#8377;)</th>
                                <th class="num" style="text-align: right;">Credit (&#8377;)</th>
                                <th class="num" style="text-align: right;">Balance (&#8377;)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $txn->date?->format('d M Y') }}</td>
                                    <td><span class="badge {{ $txn->typeBadgeClass() }}">{{ $txn->typeLabel() }}</span></td>
                                    <td style="font-weight: 600; white-space: nowrap;">{{ $txn->reference_no }}</td>
                                    <td>{{ $txn->description }}</td>
                                    <td>{{ $txn->account?->name ?? '—' }}</td>
                                    <td>{{ $txn->payment_mode ?? '—' }}</td>
                                    <td style="text-align: right;">{{ (float) $txn->debit > 0 ? number_format((float) $txn->debit, 2) : '—' }}</td>
                                    <td style="text-align: right;">{{ (float) $txn->credit > 0 ? number_format((float) $txn->credit, 2) : '—' }}</td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $txn->running_balance, 2) }}</td>
                                    <td><button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-wallet"></i></div>
                                            <div class="empty-state-title">No transactions found</div>
                                            <div class="empty-state-text">Try adjusting your filters.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $transactions, 'firstLast' => false, 'side' => 2, 'unit' => 'transactions'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Transaction Summary <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">(This Month)</span></div>
                @include('society.partials.donut', [
                    'segments' => collect($summary['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                    'centerValue' => $summary['center_value'],
                    'centerLabel' => $summary['center_label'],
                    'size' => 170,
                    'stroke' => 14,
                ])
                <div class="chart-legend" style="margin-top: 20px;">
                    @foreach($summary['segments'] as $seg)
                        <div class="legend-item">
                            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                            <span class="legend-label">{{ $seg['label'] }}</span>
                            <span class="legend-value">{{ $seg['amount'] }} ({{ $seg['pct'] }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-receipt', 'label' => 'Add Receipt', 'desc' => 'Record a new receipt', 'url' => route('society.accounting.receipts.create'), 'color' => 'green'],
            ['icon' => 'fa-credit-card', 'label' => 'Add Payment', 'desc' => 'Record a new payment', 'url' => route('society.accounting.payments.create'), 'color' => 'red'],
            ['icon' => 'fa-book', 'label' => 'Add Journal Entry', 'desc' => 'Post a journal entry', 'url' => route('society.accounting.journal-entries.create'), 'color' => 'purple'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Bank Reconciliation', 'desc' => 'Match bank statement', 'url' => route('society.accounting.bank-reconciliation'), 'color' => 'blue'],
        ]])

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>All transactions are updated in real-time. Use filters to view specific records.</span>
        </div>
    </div>
</div>
@endsection
