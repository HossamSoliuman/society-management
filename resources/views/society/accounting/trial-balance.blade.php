@extends('society.layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Trial Balance</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Trial Balance</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.trial-balance') }}" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-print"></i> Print</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding-bottom: 0;">
        @include('society.accounting._tabs', ['active' => $active])
    </div>
</div>

<form method="GET" action="{{ route('society.accounting.trial-balance') }}" class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="display: flex; gap: 12px; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label class="form-label">As on</label>
            <input type="date" name="as_on" class="form-control" value="{{ $asOn }}">
        </div>
        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Apply</button>
        <span style="font-size: 12px; color: var(--text-muted);">Balances as on {{ $trialBalance['as_on'] }}</span>
    </div>
</form>
@php $balanced = abs($trialBalance['total_debit'] - $trialBalance['total_credit']) < 0.01; @endphp

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Trial Balance <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">As on 30 May 2025</span></div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th class="num">Debit (&#8377;)</th>
                            <th class="num">Credit (&#8377;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trialBalance['rows'] as $row)
                            <tr>
                                <td style="font-weight: 600;">{{ $row['code'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                                <td class="num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align: center; padding: 24px; color: var(--text-muted);">No account balances to display.</td></tr>
                        @endforelse
                        <tr class="fin-total-row net">
                            <td colspan="2">Total</td>
                            <td class="num">{{ number_format($trialBalance['total_debit'], 2) }}</td>
                            <td class="num">{{ number_format($trialBalance['total_credit'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        @if($balanced)
            <div class="tip-banner green"><i class="fas fa-circle-check"></i><span>The trial balance is balanced — total debits equal total credits.</span></div>
        @else
            <div class="tip-banner orange"><i class="fas fa-triangle-exclamation"></i><span>The trial balance is out of balance. Review your journal entries.</span></div>
        @endif
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Balance Check</div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Total Debit</span>
                    <span style="font-weight: 600;">&#8377; {{ number_format($trialBalance['total_debit'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Total Credit</span>
                    <span style="font-weight: 600;">&#8377; {{ number_format($trialBalance['total_credit'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: var(--text-secondary);">Difference</span>
                    <span style="font-weight: 700; color: {{ $balanced ? 'var(--success)' : 'var(--danger)' }};">&#8377; {{ number_format(abs($trialBalance['total_debit'] - $trialBalance['total_credit']), 2) }}</span>
                </div>
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-chart-line', 'label' => 'Profit & Loss', 'desc' => 'Income vs expenses', 'url' => route('society.accounting.profit-loss'), 'color' => 'purple'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Balance Sheet', 'desc' => 'Financial position', 'url' => route('society.accounting.balance-sheet'), 'color' => 'green'],
            ['icon' => 'fa-sitemap', 'label' => 'Chart of Accounts', 'desc' => 'Browse all accounts', 'url' => route('society.accounting.chart-of-accounts'), 'color' => 'blue'],
        ]])
    </div>
</div>
@endsection
