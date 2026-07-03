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
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.bank-reconciliation') }}" class="btn btn-secondary"><i class="fas fa-file-import"></i> Import Statement</a>
            <a href="{{ route('society.accounting.bank-reconciliation') }}" class="btn btn-primary"><i class="fas fa-circle-check"></i> Reconcile</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding-bottom: 0;">
        @include('society.accounting._tabs', ['active' => $active])
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div class="form-row" style="margin-bottom: 4px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Bank Account</label>
                        <select class="form-control">
                            @foreach($bankAccounts as $bank)
                                <option>{{ $bank['name'] }} ({{ $bank['sub'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Statement Date</label>
                        <input type="text" class="form-control" value="30 May 2025" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Reconciliation Items</div></div>
            <div class="card-body" style="padding: 0;">
                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="num">Book (&#8377;)</th>
                            <th class="num">Bank (&#8377;)</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td style="white-space: nowrap;">{{ $row['date'] }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td class="num">{{ $row['book'] }}</td>
                                <td class="num">{{ $row['bank'] }}</td>
                                <td style="text-align: center;">
                                    @if($row['matched'])
                                        <span class="badge badge-success"><i class="fas fa-circle-check"></i> Matched</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Unmatched</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tip-banner blue"><i class="fas fa-circle-info"></i><span>Match each book entry with the corresponding bank statement line to complete reconciliation.</span></div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Reconciliation Summary</div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Balance as per Books</span>
                    <span style="font-weight: 600;">&#8377; 8,25,450.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Balance as per Bank</span>
                    <span style="font-weight: 600;">&#8377; 8,07,700.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Unmatched Items</span>
                    <span style="font-weight: 600; color: var(--warning);">2</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0 0;">
                    <span style="font-weight: 700;">Difference</span>
                    <span style="font-weight: 700; color: var(--danger);">&#8377; 17,750.00</span>
                </div>
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-file-import', 'label' => 'Import Statement', 'desc' => 'Upload bank statement', 'url' => route('society.accounting.bank-reconciliation'), 'color' => 'blue'],
            ['icon' => 'fa-list-check', 'label' => 'View Transactions', 'desc' => 'All ledger movements', 'url' => route('society.accounting.transactions'), 'color' => 'green'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Trial Balance', 'desc' => 'Debit / credit summary', 'url' => route('society.accounting.trial-balance'), 'color' => 'purple'],
        ]])
    </div>
</div>
@endsection
