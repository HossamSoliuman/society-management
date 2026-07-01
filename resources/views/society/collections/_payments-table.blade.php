@php
    /**
     * @var \Illuminate\Pagination\LengthAwarePaginator $payments
     */
@endphp
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding-left: 20px;">Receipt No.</th>
                <th>Date</th>
                <th>Member / Flat</th>
                <th>Bill Period</th>
                <th style="text-align: right;">Amount (&#8377;)</th>
                <th style="text-align: right;">Paid (&#8377;)</th>
                <th style="text-align: right;">Due (&#8377;)</th>
                <th>Payment Mode</th>
                <th>Status</th>
                <th>Collected By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                @php
                    $paid = (float) $payment->paid_amount;
                    $due = (float) $payment->balance_due;
                @endphp
                <tr>
                    <td style="padding-left: 20px; white-space: nowrap;" class="receipt-cell">
                        <a href="{{ route('society.collections.receipts.show', $payment) }}" class="receipt-no">{{ $payment->receipt_number }}</a>
                        <div class="receipt-sub">{{ $payment->bill_type }}</div>
                    </td>
                    <td style="white-space: nowrap;">
                        {{ $payment->receipt_date?->format('d M Y') }}
                        <div class="cell-sub">{{ $payment->receipt_date?->format('h:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600;">{{ $payment->member_name }}</div>
                        <div class="cell-sub">{{ $payment->flat_number }}</div>
                    </td>
                    <td>{{ $payment->bill_period }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $payment->total_due, 0) }}</td>
                    <td style="text-align: right; font-weight: 600; color: {{ $paid > 0 ? 'var(--success)' : 'var(--text-muted)' }};">{{ number_format($paid, 0) }}</td>
                    <td style="text-align: right; font-weight: 600; color: {{ $due > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">{{ number_format($due, 0) }}</td>
                    <td>
                        @if($payment->payment_mode)
                            <span class="pay-mode"><i class="fas {{ $payment->paymentModeIcon() }}"></i> {{ $payment->paymentModeLabel() }}</span>
                        @else
                            <span style="color: var(--text-muted);">&mdash;</span>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel() }}</span></td>
                    <td>{{ $payment->collected_by ?: '—' }}</td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('society.collections.receipts.show', $payment) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('society.collections.receipts.show', $payment) }}" class="action-btn" title="Receipt"><i class="fas fa-file-invoice"></i></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                            <div class="empty-state-title">No payments found</div>
                            <div class="empty-state-text">Try adjusting your filters or record a new payment.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
