@extends('society.layouts.app')

@section('title', 'Maintenance Bill List')

@php
    // Indian-grouped integer formatter, e.g. 523300 -> "5,23,300".
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
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Maintenance Bill List</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.bills.index') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <span>Bill List</span>
            </div>
        </div>
        <a href="{{ route('society.billing.bills.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Bill</a>
    </div>
</div>

{{-- KPI cards --}}
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
    @include('society.partials.stat-card', ['icon' => 'fa-file-lines', 'iconVariant' => 'primary', 'label' => 'Total Bills', 'value' => $inr($kpis['total_bills']), 'trend' => 'This Month', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Paid Bills', 'value' => $inr($kpis['paid_bills']), 'trend' => '&#8377; '.$inr($kpis['paid_amount']), 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-clock', 'iconVariant' => 'warning', 'label' => 'Pending Bills', 'value' => $inr($kpis['pending_bills']), 'trend' => '&#8377; '.$inr($kpis['pending_amount']), 'trendType' => 'warning'])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-xmark', 'iconVariant' => 'danger', 'label' => 'Overdue Bills', 'value' => $inr($kpis['overdue_bills']), 'trend' => '&#8377; '.$inr($kpis['overdue_amount']), 'trendType' => 'danger'])
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'purple', 'label' => 'Total Amount', 'value' => '&#8377; '.$inr($kpis['total_amount']), 'trend' => 'This Month', 'trendType' => 'muted'])
</div>

{{-- Filter bar --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.billing.bills.index') }}">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Search Bill</label>
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by bill no., member, flat, tower…">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Bill Month</label>
                    <select name="month" class="form-control">
                        <option value="">All Months</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}" {{ request('month') === $month ? 'selected' : '' }}>{{ $month }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Bill Cycle</label>
                    <select name="cycle" class="form-control">
                        <option value="">All Cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle }}" {{ request('cycle') === $cycle ? 'selected' : '' }}>{{ $cycle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        @foreach(['paid' => 'Paid', 'pending' => 'Pending', 'partial' => 'Partial', 'overdue' => 'Overdue'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tower / Wing</label>
                    <select name="tower" class="form-control">
                        <option value="">All Towers</option>
                        @foreach($towers as $tower)
                            <option value="{{ $tower }}" {{ request('tower') === $tower ? 'selected' : '' }}>{{ $tower }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('society.billing.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table card --}}
<div class="card">
    <div class="card-body">
        <div class="action-toolbar">
            <div class="action-toolbar-left" style="font-size: 13px; color: var(--text-secondary);">
                Show
                <select class="form-control" style="width: auto; display: inline-block; padding: 4px 8px;" onchange="window.location.href='{{ route('society.billing.bills.index') }}?per_page='+this.value">
                    @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ (int) request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                entries
            </div>
            <div class="action-toolbar-right">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle"><i class="fas fa-download"></i> Export <i class="fas fa-chevron-down" style="font-size: 9px;"></i></button>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item"><i class="fas fa-file-csv"></i> CSV</a>
                        <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Excel</a>
                        <a href="#" class="dropdown-item"><i class="fas fa-file-pdf"></i> PDF</a>
                    </div>
                </div>
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle"><i class="fas fa-layer-group"></i> Bulk Actions <i class="fas fa-chevron-down" style="font-size: 9px;"></i></button>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item"><i class="fas fa-circle-check"></i> Mark as Paid</a>
                        <a href="#" class="dropdown-item"><i class="fas fa-bell"></i> Send Reminder</a>
                        <a href="#" class="dropdown-item" style="color: var(--danger);"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-icon"><i class="fas fa-gear"></i></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px; width: 36px;"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked)"></th>
                        <th>Bill No.</th>
                        <th>Bill Month</th>
                        <th>Bill Date</th>
                        <th>Bill Cycle</th>
                        <th>Tower / Wing</th>
                        <th style="text-align: right;">Total Amount (&#8377;)</th>
                        <th style="text-align: right;">Collected (&#8377;)</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        @php
                            $collected = (float) $bill->collected_amount;
                            $total = (float) $bill->total_amount;
                            $collectedColor = $collected <= 0
                                ? 'var(--text-muted)'
                                : ($collected >= $total ? 'var(--success)' : 'var(--primary)');
                        @endphp
                        <tr>
                            <td style="padding-left: 20px;"><input type="checkbox" class="row-check" value="{{ $bill->id }}"></td>
                            <td><a href="{{ route('society.billing.bills.show', $bill) }}" style="color: var(--primary); font-weight: 600;">{{ $bill->bill_number }}</a></td>
                            <td>{{ $bill->bill_month }}</td>
                            <td>{{ $bill->bill_date?->format('d M Y') }}</td>
                            <td>{{ $bill->bill_cycle }}</td>
                            <td>{{ $bill->tower_wing }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ number_format($total, 2) }}</td>
                            <td style="text-align: right; font-weight: 600; color: {{ $collectedColor }};">{{ number_format($collected, 2) }}</td>
                            <td><span class="status-badge {{ $bill->statusBadgeClass() }}">{{ $bill->statusLabel() }}</span></td>
                            <td style="{{ $bill->status === 'overdue' ? 'color: var(--danger); font-weight: 600;' : '' }}">{{ $bill->due_date?->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('society.billing.bills.show', $bill) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.billing.bills.print', $bill) }}" target="_blank" class="action-btn" title="Print"><i class="fas fa-print"></i></a>
                                    <div class="dropdown">
                                        <button type="button" class="action-btn dropdown-toggle" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            <a href="{{ route('society.billing.bills.show', $bill) }}" class="dropdown-item"><i class="fas fa-eye"></i> View</a>
                                            <a href="{{ route('society.billing.bills.print', $bill) }}" target="_blank" class="dropdown-item"><i class="fas fa-print"></i> Print</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-file-invoice"></i></div>
                                    <div class="empty-state-title">No bills found</div>
                                    <div class="empty-state-text">Try adjusting your filters or create a new bill.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $bills, 'side' => 2, 'unit' => 'entries'])
@endsection
