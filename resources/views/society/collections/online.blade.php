@extends('society.layouts.app')

@section('title', 'Online Payments')

@php
    $tabs = ['all' => 'All Payments', 'received' => 'Received', 'pending' => 'Pending', 'overdue' => 'Overdue', 'refunded' => 'Refunded'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Online Payments</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Collections</a>
                <span class="breadcrumb-separator">/</span>
                <span>Online Payments</span>
            </div>
        </div>
        <a href="{{ route('society.collections.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Record Payment</a>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.collections.online') }}" style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 16px; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Payment Mode</label>
                        <select name="mode" class="form-control">
                            <option value="">All Modes</option>
                            @foreach(['upi' => 'UPI', 'card' => 'Card', 'net_banking' => 'Net Banking'] as $val => $label)
                                <option value="{{ $val }}" {{ request('mode') === $val ? 'selected' : '' }}>{{ $label }}</option>
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
                    <a href="{{ route('society.collections.online') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="tab-pills">
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('society.collections.online', ['tab' => $key]) }}" class="tab-pill {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                @include('society.collections._payments-table', ['payments' => $payments])
            </div>
        </div>

        @include('society.partials.pagination', ['items' => $payments, 'firstLast' => true, 'side' => 2, 'unit' => 'transactions'])
    </div>

    <div>
        @include('society.collections._collections-rail', ['overview' => $overview, 'recent' => $recent])
    </div>
</div>
@endsection
