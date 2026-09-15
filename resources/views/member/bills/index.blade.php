@extends('member.layouts.app')

@section('title', 'My Bills')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Bills</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>My Bills</span>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => $stats['outstanding'] > 0 ? 'danger' : 'success', 'label' => 'Outstanding', 'value' => '&#8377; '.format_inr($stats['outstanding'])])
    @include('society.partials.stat-card', ['icon' => 'fa-clock', 'iconVariant' => 'warning', 'label' => 'Open Bills', 'value' => $stats['open']])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-xmark', 'iconVariant' => 'danger', 'label' => 'Overdue', 'value' => $stats['overdue']])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Paid Bills', 'value' => $stats['paid']])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('member.bills.index') }}" style="display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search</label>
                <div class="header-search" style="max-width: none;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Bill number or month">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    @foreach(['pending' => 'Pending', 'partial' => 'Partially paid', 'overdue' => 'Overdue', 'paid' => 'Paid'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('member.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Bill No.</th>
                        <th>Month</th>
                        <th>Bill Date</th>
                        <th>Due Date</th>
                        <th style="text-align: right;">Total (&#8377;)</th>
                        <th style="text-align: right;">Paid (&#8377;)</th>
                        <th style="text-align: right;">Outstanding (&#8377;)</th>
                        <th>Status</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        <tr>
                            <td style="padding-left: 20px;"><a href="{{ route('member.bills.show', $bill) }}" style="font-weight: 600;">{{ $bill->bill_number }}</a></td>
                            <td>{{ $bill->bill_month }}</td>
                            <td>{{ $bill->bill_date?->format('d M Y') }}</td>
                            <td>{{ $bill->due_date?->format('d M Y') }}</td>
                            <td style="text-align: right;">{{ format_inr($bill->total_amount) }}</td>
                            <td style="text-align: right;">{{ format_inr($bill->collected_amount) }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ format_inr($bill->outstanding_amount) }}</td>
                            <td>@include('society.partials.status-badge', ['class' => $bill->statusBadgeClass(), 'label' => $bill->statusLabel()])</td>
                            <td style="text-align: right; padding-right: 20px;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('member.bills.show', $bill) }}" class="btn btn-outline-secondary btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('member.bills.pdf', $bill) }}" class="btn btn-outline-secondary btn-sm" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                    @if($bill->outstanding_amount > 0 && $bill->status !== 'cancelled')
                                        <form method="POST" action="{{ route('member.bills.pay', $bill) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-credit-card"></i> Pay</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align: center; padding: 32px; color: var(--text-muted);">No bills found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('society.partials.pagination', ['items' => $bills, 'unit' => 'bills'])
@endsection
