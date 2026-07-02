@extends('society.layouts.app')

@section('title', 'Expenses')

@php
    // Indian-grouped integer formatter, e.g. 945600 -> "9,45,600".
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
    $tabs = ['all' => 'All Expenses', 'paid' => 'Paid', 'pending' => 'Pending', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Expenses</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <span>All Expenses</span>
            </div>
        </div>
        <a href="{{ route('society.expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Expense</a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- KPI cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-wallet"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Expenses (This Month)</div>
                    <div class="stat-value">&#8377; {{ $inr($kpis['month_total']) }}</div>
                    <div class="stat-trend" style="color: var(--success);"><i class="fas fa-arrow-down"></i><span>{{ $kpis['month_trend'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-chart-bar"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Expenses (This Year)</div>
                    <div class="stat-value">&#8377; {{ $inr($kpis['year_total']) }}</div>
                    <div class="stat-trend" style="color: var(--success);"><i class="fas fa-arrow-down"></i><span>{{ $kpis['year_trend'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-calendar-days"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pending Payments</div>
                    <div class="stat-value">&#8377; {{ $inr($kpis['pending']) }}</div>
                    <div class="stat-trend" style="color: var(--warning);"><span>{{ $kpis['pending_bills'] }} Bills</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-file-lines"></i></div>
                <div class="stat-info">
                    <div class="stat-label">This Month Budget</div>
                    <div class="stat-value">&#8377; {{ $inr($kpis['budget']) }}</div>
                    <div class="stat-trend" style="color: var(--warning); margin-bottom: 6px;"><span>{{ $kpis['budget_used_pct'] }}% Used</span></div>
                    <div class="progress-bar"><div class="progress-bar-fill" style="width: {{ $kpis['budget_used_pct'] }}%; background: var(--orange);"></div></div>
                </div>
            </div>
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.expenses.index') }}">
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from" value="{{ request('from', '2024-05-01') }}" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to" value="{{ request('to', '2024-05-31') }}" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Vendor</label>
                            <select name="vendor" class="form-control">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Payment Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                @foreach(['paid' => 'Paid', 'pending' => 'Pending', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 2fr auto auto; gap: 16px; align-items: end;">
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
                            <label class="form-label">Reference Type</label>
                            <select name="ref_type" class="form-control">
                                <option value="">All Types</option>
                                <option>Bill</option>
                                <option>Invoice</option>
                                <option>Receipt</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Search</label>
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by expense title, vendor, reference no...">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.expenses.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabs + table --}}
        <div class="card">
            <div class="card-body">
                <div class="tabs">
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('society.expenses.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}" class="tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Expense Date</th>
                                <th>Expense Title</th>
                                <th>Category</th>
                                <th>Vendor</th>
                                <th>Amount (&#8377;)</th>
                                <th>Payment Mode</th>
                                <th>Status</th>
                                <th>Reference No.</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expenses->firstItem() + $loop->index }}</td>
                                    <td style="white-space: nowrap;">{{ $expense->expense_date?->format('d M Y') }}</td>
                                    <td style="font-weight: 500;">{{ $expense->title }}</td>
                                    <td>
                                        @if($expense->category)
                                            <span class="badge {{ $expense->category->badgeClass() }}">{{ $expense->category->name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->vendor?->name ?? '—' }}</td>
                                    <td style="font-weight: 600; white-space: nowrap;">&#8377; {{ number_format((float) $expense->amount, 0) }}</td>
                                    <td>
                                        <span class="pay-pill"><i class="fas {{ $expense->paymentModeIcon() }}"></i> {{ $expense->payment_mode }}</span>
                                    </td>
                                    <td><span class="status-badge {{ $expense->statusBadgeClass() }}">{{ $expense->statusLabel() }}</span></td>
                                    <td style="white-space: nowrap;">{{ $expense->reference_no }}</td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.expenses.edit', $expense) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                            @if($expense->payment_status === 'pending')
                                                <a href="{{ route('society.expenses.edit', $expense) }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                            @else
                                                <a href="#" class="action-btn" title="Print"><i class="fas fa-print"></i></a>
                                            @endif
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
                                            <div class="empty-state-title">No expenses found</div>
                                            <div class="empty-state-text">Try adjusting your filters or add a new expense.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $expenses, 'firstLast' => true, 'side' => 2, 'unit' => 'entries'])
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        {{-- Expense Overview --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Expense Overview <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">(This Month)</span></div>
                @include('society.partials.donut', [
                    'segments' => collect($overview['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
                    'centerValue' => $overview['center_value'],
                    'centerLabel' => $overview['center_label'],
                    'size' => 170,
                    'stroke' => 14,
                ])
                <div class="chart-legend" style="margin-top: 20px;">
                    @foreach($overview['segments'] as $seg)
                        <div class="legend-item">
                            <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                            <span class="legend-label">{{ $seg['label'] }}</span>
                            <span class="legend-value">{!! $seg['amount'] !!} ({{ $seg['pct'] }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Categories --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Top Categories</div>
                @foreach($topCategories as $cat)
                    <div style="margin-bottom: {{ $loop->last ? '0' : '16px' }};">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                            <span style="color: var(--text-secondary);">{{ $cat['label'] }}</span>
                            <span style="font-weight: 600;">{!! $cat['amount'] !!} ({{ $cat['pct'] }})</span>
                        </div>
                        <div class="progress-bar"><div class="progress-bar-fill" style="width: {{ $cat['width'] }}%; background: {{ $cat['color'] }};"></div></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add New Expense', 'desc' => 'Record a new expense', 'url' => route('society.expenses.create'), 'color' => 'orange'],
            ['icon' => 'fa-money-check-dollar', 'label' => 'Pay Pending Bills', 'desc' => 'View and pay pending bills', 'url' => route('society.expenses.index', ['tab' => 'pending']), 'color' => 'green'],
            ['icon' => 'fa-list', 'label' => 'Expense Categories', 'desc' => 'Manage expense categories', 'url' => route('society.expenses.categories.index'), 'color' => 'blue'],
            ['icon' => 'fa-store', 'label' => 'Vendors', 'desc' => 'Manage vendors', 'url' => route('society.expenses.vendors.index'), 'color' => 'purple'],
        ]])
    </div>
</div>
@endsection
