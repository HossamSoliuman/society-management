@extends('society.layouts.app')

@section('title', 'Expense Reports')

@php
    $total = (float) $summary['total'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Expense Reports</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <span>Expense Reports</span>
            </div>
        </div>
        <div class="action-toolbar-right" style="display: flex; gap: 8px;">
            <a href="#" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Export PDF</a>
            <a href="#" class="btn btn-secondary"><i class="fas fa-file-excel"></i> Export Excel</a>
            <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
</div>

{{-- Filter card --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.expenses.reports') }}">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
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
                <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('society.expenses.reports') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- KPI cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="kpi-tinted expense">
        <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-bottom: 6px;">Total Expenses</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--danger);">&#8377; {{ number_format((float) $summary['total'], 2) }}</div>
    </div>
    <div class="kpi-tinted income">
        <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-bottom: 6px;">Total Paid</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--success);">&#8377; {{ number_format((float) $summary['paid'], 2) }}</div>
    </div>
    <div class="kpi-tinted margin">
        <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-bottom: 6px;">Total Due</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--warning);">&#8377; {{ number_format((float) $summary['due'], 2) }}</div>
    </div>
    <div class="kpi-tinted profit">
        <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-bottom: 6px;">Total Entries</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--info);">{{ number_format((int) $summary['count']) }}</div>
    </div>
</div>

{{-- Category breakdown --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Category-wise Breakdown</div>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="fin-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="num">Entries</th>
                    <th class="num">Amount (&#8377;)</th>
                    <th style="width: 30%;">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($breakdown as $row)
                    @php
                        $rowTotal = (float) $row->total;
                        $pct = $total > 0 ? round(($rowTotal / $total) * 100, 1) : 0;
                        $color = $row->category?->color ?? 'gray';
                        [$bg, $fg] = \App\Models\ExpenseCategory::tint($color);
                    @endphp
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $row->category?->icon ?? 'fa-tag' }}"></i></span>
                                <span style="font-weight: 600;">{{ $row->category?->name ?? 'Uncategorized' }}</span>
                            </div>
                        </td>
                        <td class="num">{{ $row->entries }}</td>
                        <td class="num" style="font-weight: 600;">&#8377; {{ number_format($rowTotal, 0) }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar" style="flex: 1;"><div class="progress-bar-fill" style="width: {{ $pct }}%; background: {{ $fg }};"></div></div>
                                <span style="font-size: 12px; font-weight: 600; min-width: 44px; text-align: right;">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
                                <div class="empty-state-title">No data for the selected filters</div>
                                <div class="empty-state-text">Adjust the date range or filters to see the breakdown.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($breakdown->isNotEmpty())
                <tfoot>
                    <tr class="fin-total-row expense">
                        <td>Total</td>
                        <td class="num">{{ (int) $summary['count'] }}</td>
                        <td class="num">&#8377; {{ number_format($total, 0) }}</td>
                        <td>100%</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="tip-banner blue">
    <i class="fas fa-circle-info"></i>
    <span>Use the filters above to narrow the report by date range, category or vendor, then export or print the result.</span>
</div>
@endsection
