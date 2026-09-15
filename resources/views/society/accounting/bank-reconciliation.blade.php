@extends('society.layouts.app')

@section('title', 'Bank Reconciliation')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Bank Reconciliation</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Bank Reconciliation</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding-bottom: 0;">
        @include('society.accounting._tabs', ['active' => $active])
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.accounting.bank-reconciliation') }}" class="form-row-3" style="margin-bottom: 0; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Bank Account</label>
                        <select name="account" class="form-control" onchange="this.form.submit()">
                            @forelse($bankAccounts as $bank)
                                <option value="{{ $bank->id }}" {{ $account && $account->id === $bank->id ? 'selected' : '' }}>{{ $bank->name }} ({{ $bank->code }})</option>
                            @empty
                                <option value="">No bank accounts — mark one in the Chart of Accounts</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">From</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">To</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="date" name="to" class="form-control" value="{{ $to }}">
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($account)
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('society.accounting.bank-reconciliation.import') }}" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: end; flex-wrap: wrap;">
                        @csrf
                        <input type="hidden" name="account_id" value="{{ $account->id }}">
                        <div class="form-group" style="margin-bottom: 0; flex: 1;">
                            <label class="form-label">Import bank statement (CSV / XLSX with Date, Description, Ref No, Debit, Credit, Balance)</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx,.xls" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-import"></i> Import &amp; Auto-match</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header"><div class="card-title">Reconciliation Items</div></div>
            <div class="card-body" style="padding: 0;">
                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="num">Book (&#8377;)</th>
                            <th class="num">Bank (&#8377;)</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td style="white-space: nowrap;">{{ $row['date'] }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td style="color: var(--text-muted); font-size: 12px;">{{ $row['reference'] ?? '—' }}</td>
                                <td class="num">{{ $row['book'] }}</td>
                                <td class="num">{{ $row['bank'] }}</td>
                                <td style="text-align: center;">
                                    @if($row['matched'])
                                        <form method="POST" action="{{ route('society.accounting.bank-reconciliation.unmatch', $row['id']) }}" style="display: inline;">
                                            @csrf
                                            <span class="badge badge-success"><i class="fas fa-circle-check"></i> Matched</span>
                                            <button type="submit" class="btn btn-link btn-sm" style="font-size: 11px;">undo</button>
                                        </form>
                                    @elseif($row['id'])
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Bank only</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-book"></i> Book only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">Import a statement to start reconciling.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tip-banner blue"><i class="fas fa-circle-info"></i><span>Statement lines are matched to ledger entries on the same account by amount within ±{{ \App\Services\BankReconciliationService::DATE_TOLERANCE_DAYS }} days; a matching reference number wins ties.</span></div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Reconciliation Summary</div>
                @if($summary)
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span style="color: var(--text-secondary);">Balance as per Books</span>
                        <span style="font-weight: 600;">&#8377; {{ $summary['book_balance'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span style="color: var(--text-secondary);">Balance as per Bank</span>
                        <span style="font-weight: 600;">&#8377; {{ $summary['bank_balance'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span style="color: var(--text-secondary);">Statement lines / matched</span>
                        <span style="font-weight: 600;">{{ $summary['statement_lines'] }} / {{ $summary['matched'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                        <span style="color: var(--text-secondary);">Unmatched (bank / book)</span>
                        <span style="font-weight: 600; color: var(--warning);">{{ $summary['unmatched_bank'] }} / {{ $summary['unmatched_book'] }}</span>
                    </div>
                @else
                    <div style="font-size: 13px; color: var(--text-muted);">Select a bank account to see the summary.</div>
                @endif
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-list-check', 'label' => 'View Transactions', 'desc' => 'All ledger movements', 'url' => route('society.accounting.transactions'), 'color' => 'green'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Trial Balance', 'desc' => 'Debit / credit summary', 'url' => route('society.accounting.trial-balance'), 'color' => 'purple'],
        ]])
    </div>
</div>
@endsection
