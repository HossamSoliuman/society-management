@php
    /**
     * @var array $overview  donut segments + center + total
     * @var \Illuminate\Support\Collection $recent
     */
@endphp
{{-- Collection Overview --}}
<div class="card">
    <div class="card-body">
        <div class="card-title-row" style="margin-bottom: 12px;">
            <div class="section-title" style="font-size: 15px; margin: 0;">Collection Overview</div>
            <x-period-select :url="route('society.collections.overview-data')" target="collections-overview" :selected="$overview['range']" />
        </div>
        <div id="card-collections-overview">
            @include('society.collections._overview', ['overview' => $overview])
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">Quick Links</div>
        <div class="quick-link-list">
            <a href="{{ route('society.collections.create') }}" class="quick-link-item">
                <span class="ql-icon"><i class="fas fa-money-bill-wave"></i></span>
                <span><span class="ql-title">Record Payment</span><br><span class="ql-sub">Add new payment</span></span>
            </a>
            <a href="{{ route('society.collections.pending-dues') }}" class="quick-link-item">
                <span class="ql-icon"><i class="fas fa-clock"></i></span>
                <span><span class="ql-title">Pending Dues</span><br><span class="ql-sub">View pending collections</span></span>
            </a>
            <a href="{{ route('society.collections.receipts.index') }}" class="quick-link-item">
                <span class="ql-icon"><i class="fas fa-file-invoice"></i></span>
                <span><span class="ql-title">Payment Receipts</span><br><span class="ql-sub">View all receipts</span></span>
            </a>
            <a href="{{ route('society.collections.online') }}" class="quick-link-item">
                <span class="ql-icon"><i class="fas fa-credit-card"></i></span>
                <span><span class="ql-title">Online Payments</span><br><span class="ql-sub">View transactions</span></span>
            </a>
        </div>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">Recent Transactions</div>
        @foreach($recent as $txn)
            <div class="recent-txn">
                <div>
                    <div class="txn-no">{{ $txn->receipt_number }}</div>
                    <div class="txn-date">{{ $txn->receipt_date?->format('d M Y') }}</div>
                </div>
                <div class="txn-amount">&#8377; {{ number_format((float) $txn->paid_amount, 0) }}</div>
            </div>
        @endforeach
        <a href="{{ route('society.collections.receipts.index') }}" style="display: inline-block; margin-top: 12px; color: var(--primary); font-weight: 600; font-size: 13px; text-decoration: none;">View All Transactions <i class="fas fa-arrow-right" style="font-size: 11px;"></i></a>
    </div>
</div>
