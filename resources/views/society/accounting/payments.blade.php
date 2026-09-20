@extends('society.layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payments</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Payments</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.payments.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Payment</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body" style="padding-bottom: 0;">
                @include('society.accounting._tabs', ['active' => $active])
            </div>
        </div>

        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-credit-card"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Payments</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--danger);"><span>&#8377; {{ $stats['total_amount'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Amount Paid</div>
                    <div class="stat-value">&#8377; {{ $stats['total_amount'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pending Payments</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-trend" style="color: var(--warning);"><span>&#8377; {{ $stats['pending_amount'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Average Payment Value</div>
                    <div class="stat-value">&#8377; {{ $stats['average'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.accounting.payments') }}">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1.4fr auto; gap: 12px; align-items: end;">
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
                            <label class="form-label">Account</label>
                            <select name="account" class="form-control">
                                <option value="">All Accounts</option>
                                @foreach($accountsForFilter as $account)
                                    <option value="{{ $account->id }}" {{ request('account') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="header-search" style="max-width: none;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search payment no., payee, purpose...">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payment No.</th>
                                <th>Date</th>
                                <th>Payee</th>
                                <th>Purpose</th>
                                <th>Mode</th>
                                <th class="num" style="text-align: right;">Amount (&#8377;)</th>
                                <th>Account</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payments->firstItem() + $loop->index }}</td>
                                    <td style="font-weight: 600; white-space: nowrap;">{{ $payment->payment_no }}</td>
                                    <td style="white-space: nowrap;">{{ $payment->date?->format('d M Y') }}</td>
                                    <td>{{ $payment->payee }}</td>
                                    <td>{{ $payment->purpose }}</td>
                                    <td><span class="pay-pill"><i class="fas {{ $payment->modeIcon() }}"></i> {{ $payment->mode }}</span></td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ $payment->account?->name ?? '—' }}</td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.accounting.payments') }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-credit-card"></i></div>
                                            <div class="empty-state-title">No payments found</div>
                                            <div class="empty-state-text">Try adjusting your filters or add a new payment.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $payments, 'firstLast' => false, 'side' => 2, 'unit' => 'payments'])
            </div>
        </div>
    </div>

    <div>
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add Payment', 'desc' => 'Record a new payment', 'url' => route('society.accounting.payments.create'), 'color' => 'red'],
            ['icon' => 'fa-receipt', 'label' => 'Add Receipt', 'desc' => 'Record a new receipt', 'url' => route('society.accounting.receipts.create'), 'color' => 'green'],
            ['icon' => 'fa-book', 'label' => 'Add Journal Entry', 'desc' => 'Post a journal entry', 'url' => route('society.accounting.journal-entries.create'), 'color' => 'purple'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Bank Reconciliation', 'desc' => 'Match bank statement', 'url' => route('society.accounting.bank-reconciliation'), 'color' => 'blue'],
        ]])

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Payments reduce your bank/cash balance and are posted against the selected expense account.</span>
        </div>
    </div>
</div>
@endsection
