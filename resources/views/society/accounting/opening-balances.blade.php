@extends('society.layouts.app')

@section('title', 'Opening Balances')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Opening Balances</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Opening Balances</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.opening-balances') }}" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Balances</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Opening Balances <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">Financial Year 2025-2026</span></div>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Group</th>
                            <th class="num">Opening Balance (&#8377;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td style="font-weight: 600;">{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td>{{ $account->group?->name ?? '—' }}</td>
                                <td class="num" style="width: 200px;">
                                    <input type="number" step="0.01" class="form-control" style="text-align: right;" value="{{ number_format((float) $account->opening_balance, 2, '.', '') }}">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align: center; padding: 24px; color: var(--text-muted);">No detail accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tip-banner orange"><i class="fas fa-lightbulb"></i><span>Enter the closing balances of the previous financial year as opening balances for the current year.</span></div>
    </div>

    <div>
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Opening balances set the starting point for each account at the beginning of the financial year. They feed directly into the Trial Balance and Balance Sheet.</span>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-scale-balanced', 'label' => 'Trial Balance', 'desc' => 'Verify balances', 'url' => route('society.accounting.trial-balance'), 'color' => 'green'],
            ['icon' => 'fa-sitemap', 'label' => 'Chart of Accounts', 'desc' => 'Manage accounts', 'url' => route('society.accounting.chart-of-accounts'), 'color' => 'blue'],
        ]])
    </div>
</div>
@endsection
