@php
    /**
     * Accounting in-page tab strip.
     *
     * @var string $active  the active tab key
     */
    $tabs = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'society.accounting.index'],
        'transactions' => ['label' => 'Transactions', 'route' => 'society.accounting.transactions'],
        'receipts' => ['label' => 'Receipts', 'route' => 'society.accounting.receipts'],
        'payments' => ['label' => 'Payments', 'route' => 'society.accounting.payments'],
        'journal-entries' => ['label' => 'Journal Entries', 'route' => 'society.accounting.journal-entries'],
        'bank-reconciliation' => ['label' => 'Bank Reconciliation', 'route' => 'society.accounting.bank-reconciliation'],
        'trial-balance' => ['label' => 'Trial Balance', 'route' => 'society.accounting.trial-balance'],
        'profit-loss' => ['label' => 'Profit & Loss', 'route' => 'society.accounting.profit-loss'],
        'balance-sheet' => ['label' => 'Balance Sheet', 'route' => 'society.accounting.balance-sheet'],
    ];
@endphp
<div class="tabs" style="overflow-x: auto;">
    @foreach($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}" class="tab {{ $active === $key ? 'active' : '' }}" style="white-space: nowrap;">{{ $tab['label'] }}</a>
    @endforeach
</div>
