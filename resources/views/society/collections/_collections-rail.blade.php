@php
    /**
     * @var array $overview  donut segments + center + total
     * @var \Illuminate\Support\Collection $recent
     */
@endphp
{{-- Collection Overview --}}
<div class="card">
    <div class="card-body">
        <div class="section-title" style="font-size: 15px;">Collection Overview</div>
        @include('society.partials.donut', [
            'segments' => collect($overview['segments'])->map(fn ($s) => ['value' => $s['value'], 'color' => $s['color']])->all(),
            'centerValue' => $overview['center_value'],
            'centerLabel' => $overview['center_label'],
            'size' => 170,
            'stroke' => 14,
        ])
        <div class="chart-legend" style="margin-top: 20px;">
            @foreach($overview['segments'] as $seg)
                <div class="legend-item">
                    <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                    <span class="legend-label">{{ $seg['label'] }}</span>
                    <span class="legend-value">{!! $seg['amount'] !!} ({{ $seg['pct'] }})</span>
                </div>
            @endforeach
        </div>
        <div class="summary-item" style="margin-top: 12px; border-top: 1px solid var(--border-color); padding-top: 14px;">
            <span class="summary-label" style="font-weight: 600; color: var(--text-primary);">Total</span>
            <span class="summary-value">{!! $overview['total'] !!}</span>
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
