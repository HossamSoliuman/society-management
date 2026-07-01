@extends('society.layouts.app')

@section('title', 'Collections')

@php
    // Indian-grouped integer formatter, e.g. 124850 -> "1,24,850".
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
    $tabs = ['all' => 'All Payments', 'received' => 'Received', 'pending' => 'Pending', 'overdue' => 'Overdue', 'refunded' => 'Refunded'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Collections</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Collections</a>
                <span class="breadcrumb-separator">/</span>
                <span>Payment Collection</span>
            </div>
        </div>
        <a href="{{ route('society.collections.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Record Payment <i class="fas fa-chevron-down" style="font-size: 9px;"></i></a>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- KPI cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'success', 'label' => 'Total Collected (This Month)', 'value' => '&#8377; '.$inr($kpis['month_collected']), 'trend' => '18.6% vs last month', 'trendType' => 'up'])
            @include('society.partials.stat-card', ['icon' => 'fa-wallet', 'iconVariant' => 'info', 'label' => 'Total Collected (This Year)', 'value' => '&#8377; '.$inr($kpis['year_collected']), 'trend' => '22.4% vs last year', 'trendType' => 'up'])
            @include('society.partials.stat-card', ['icon' => 'fa-clock', 'iconVariant' => 'warning', 'label' => 'Pending Collections', 'value' => '&#8377; '.$inr($kpis['pending']), 'trend' => $kpis['pending_sub'], 'trendType' => 'warning'])
            @include('society.partials.stat-card', ['icon' => 'fa-file-invoice', 'iconVariant' => 'danger', 'label' => 'Overdue Amount', 'value' => '&#8377; '.$inr($kpis['overdue']), 'trend' => $kpis['overdue_sub'], 'trendType' => 'danger'])
        </div>

        {{-- Filter card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.collections.index') }}">
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Building</label>
                            <select name="building" class="form-control"><option>All Buildings</option></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Wing / Block</label>
                            <select name="wing" class="form-control"><option>All Wings</option></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Payment Mode</label>
                            <select name="mode" class="form-control">
                                <option value="">All Modes</option>
                                @foreach(['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'net_banking' => 'Net Banking', 'cheque' => 'Cheque', 'other' => 'Other'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('mode') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 2fr auto auto; gap: 16px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                @foreach(['paid' => 'Paid', 'partial' => 'Partial', 'pending' => 'Pending', 'overdue' => 'Overdue', 'refunded' => 'Refunded'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Collected By</label>
                            <select name="collected_by" class="form-control">
                                <option value="">All Users</option>
                                @foreach($collectors as $c)
                                    <option value="{{ $c }}" {{ request('collected_by') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Search</label>
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by member name, flat no, or receipt no.">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('society.collections.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabs + Export --}}
        <div class="card">
            <div class="card-body">
                <div class="action-toolbar">
                    <div class="tab-pills" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
                        @foreach($tabs as $key => $label)
                            <a href="{{ route('society.collections.index', ['tab' => $key]) }}" class="tab-pill {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle"><i class="fas fa-download"></i> Export <i class="fas fa-chevron-down" style="font-size: 9px;"></i></button>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item"><i class="fas fa-file-csv"></i> CSV</a>
                            <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Excel</a>
                            <a href="#" class="dropdown-item"><i class="fas fa-file-pdf"></i> PDF</a>
                        </div>
                    </div>
                </div>

                @include('society.collections._payments-table', ['payments' => $payments])
            </div>
        </div>

        @include('society.partials.pagination', ['items' => $payments, 'firstLast' => true, 'side' => 2, 'unit' => 'entries'])
    </div>

    {{-- Right rail --}}
    <div>
        @include('society.collections._collections-rail', ['overview' => $overview, 'recent' => $recent])
    </div>
</div>
@endsection
