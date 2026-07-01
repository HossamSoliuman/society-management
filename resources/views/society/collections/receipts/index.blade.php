@extends('society.layouts.app')

@section('title', 'Payment Receipts')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment Receipts</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Collections</a>
                <span class="breadcrumb-separator">/</span>
                <span>Payment Receipts</span>
            </div>
        </div>
        <a href="{{ route('society.collections.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Record Payment</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.collections.receipts.index') }}" style="display: grid; grid-template-columns: 2fr auto auto; gap: 16px; align-items: end; margin-bottom: 8px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search</label>
                <div class="header-search" style="max-width: none;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by member name, flat no, or receipt no.">
                </div>
            </div>
            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.collections.receipts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @include('society.collections._payments-table', ['payments' => $payments])
    </div>
</div>

@include('society.partials.pagination', ['items' => $payments, 'firstLast' => true, 'side' => 2, 'unit' => 'receipts'])
@endsection
