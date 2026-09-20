@extends('society.layouts.app')

@section('title', 'Receipts')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Receipts</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Receipts</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.receipts') }}" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</a>
            <a href="{{ route('society.accounting.receipts.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Receipt</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Tabs --}}
        <div class="card">
            <div class="card-body" style="padding-bottom: 0;">
                @include('society.accounting._tabs', ['active' => $active])
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Receipts</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>&#8377; {{ $stats['total_amount'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Amount Received</div>
                    <div class="stat-value">&#8377; {{ $stats['total_amount'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pending Receipts</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-trend" style="color: var(--warning);"><span>&#8377; {{ $stats['pending_amount'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Average Receipt Value</div>
                    <div class="stat-value">&#8377; {{ $stats['average'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
        </div>

        {{-- Filter row --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.accounting.receipts') }}">
                    <div style="display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr 1.4fr auto; gap: 12px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Date Range</label>
                            <input type="text" class="form-control" value="01 May 2025 - 30 May 2025" readonly>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Receipt Type</label>
                            <select name="type" class="form-control">
                                <option value="">All Receipt Types</option>
                                @foreach($receiptTypes as $rt)
                                    <option value="{{ $rt['value'] }}" {{ request('type') === $rt['value'] ? 'selected' : '' }}>{{ $rt['label'] }}</option>
                                @endforeach
                            </select>
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
                            <label class="form-label">Location</label>
                            <select name="location" class="form-control">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" {{ request('location') === $location ? 'selected' : '' }}>{{ $location }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search receipt no., ref no., payer...">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Payer / Flat</th>
                                <th>Receipt Type</th>
                                <th>Reference No.</th>
                                <th>Mode of Payment</th>
                                <th class="num" style="text-align: right;">Amount (&#8377;)</th>
                                <th>Account</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $receipt)
                                <tr>
                                    <td>{{ $receipts->firstItem() + $loop->index }}</td>
                                    <td><a href="{{ route('society.accounting.receipts') }}" style="color: var(--primary); font-weight: 600; white-space: nowrap;">{{ $receipt->receipt_no }}</a></td>
                                    <td style="white-space: nowrap;">
                                        <div>{{ $receipt->date?->format('d M Y') }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $receipt->date?->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;">{{ $receipt->payer_name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $receipt->flat_no }}</div>
                                    </td>
                                    <td><span class="badge {{ $receipt->typeBadgeClass() }}">{{ $receipt->typeLabel() }}</span></td>
                                    <td style="white-space: nowrap;">{{ $receipt->reference_no ?? '—' }}</td>
                                    <td><span class="pay-pill"><i class="fas {{ $receipt->modeIcon() }}"></i> {{ $receipt->mode_of_payment }}</span></td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $receipt->amount, 2) }}</td>
                                    <td>{{ $receipt->account?->name ?? '—' }}</td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.accounting.receipts') }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('society.accounting.receipts') }}" class="action-btn" title="Print"><i class="fas fa-print"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
                                            <div class="empty-state-title">No receipts found</div>
                                            <div class="empty-state-text">Try adjusting your filters or add a new receipt.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $receipts, 'firstLast' => false, 'side' => 2, 'unit' => 'receipts'])
            </div>
        </div>

        <div class="tip-banner blue"><i class="fas fa-circle-info"></i><span>Receipts are automatically linked with invoices and accounts for accurate tracking and reporting.</span></div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Receipt Summary --}}
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Receipt Summary</div>
                    <a href="{{ route('society.accounting.receipts') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All</a>
                </div>
                @foreach($receiptSummary as $row)
                    <div style="display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 13px;">
                        <span class="legend-dot" style="background: {{ $row['color'] }};"></span>
                        <span style="flex: 1; color: var(--text-secondary);">{{ $row['label'] }}</span>
                        <span style="font-weight: 600;">&#8377; {{ $row['amount'] }}</span>
                    </div>
                @endforeach
                <div style="display: flex; justify-content: space-between; padding: 10px 0 0; font-weight: 700;">
                    <span>Total</span>
                    <span>&#8377; 3,25,600</span>
                </div>
            </div>
        </div>

        {{-- Payment Modes --}}
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Payment Modes</div>
                    <a href="{{ route('society.accounting.receipts') }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All</a>
                </div>
                @foreach($paymentModesRail as $row)
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: var(--gray-100); color: var(--text-secondary);"><i class="fas {{ $row['icon'] }}"></i></span>
                        <div class="rli-main"><div class="rli-title">{{ $row['label'] }}</div></div>
                        <span class="rli-meta">&#8377; {{ $row['amount'] }} <span style="color: var(--text-muted); font-weight: 400;">({{ $row['pct'] }})</span></span>
                    </div>
                @endforeach
                <div style="display: flex; justify-content: space-between; padding: 10px 0 0; font-weight: 700;">
                    <span>Total</span>
                    <span>&#8377; 3,25,600</span>
                </div>
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add Receipt', 'desc' => 'Record a new receipt', 'url' => route('society.accounting.receipts.create'), 'color' => 'green'],
            ['icon' => 'fa-gear', 'label' => 'Receipt Settings', 'desc' => 'Configure receipts', 'url' => route('society.accounting.receipts'), 'color' => 'blue'],
            ['icon' => 'fa-print', 'label' => 'Receipt Print Format', 'desc' => 'Customize print layout', 'url' => route('society.accounting.receipts'), 'color' => 'purple'],
            ['icon' => 'fa-chart-line', 'label' => 'Receipt Report', 'desc' => 'View receipt reports', 'url' => route('society.accounting.receipts'), 'color' => 'orange'],
        ]])
    </div>
</div>
@endsection
