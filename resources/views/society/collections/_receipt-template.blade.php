@php
    /**
     * Printable payment receipt (design §5.2). Reused by the receipt detail screen
     * and print/download output.
     *
     * @var \App\Models\CollectionPayment $payment
     * @var \App\Models\Society|null $society
     */
    $societyName = $society->name ?? 'Green Meadows Society';
    $societyAddress = trim(collect([$society->address_line_1 ?? null, $society->city ?? null])->filter()->implode(', ')) ?: 'Sector 15, Nerul, Navi Mumbai - 400706';
    $societyEmail = $society->primary_email ?? 'contact@greenmeadows.in';
    $societyPhone = $society->primary_mobile ?? '9876543210';
    $isPaid = $payment->status === 'paid';
    $memberMobile = $payment->member_mobile ?: $payment->member?->mobile;
    $memberEmail = $payment->member_email ?: $payment->member?->email;
@endphp
<div class="receipt-preview receipt-print-area">
    <div class="receipt-watermark"><i class="fas fa-building"></i></div>
    <div class="receipt-body">
        {{-- Header band --}}
        <div class="receipt-header">
            <div class="receipt-logo">
                <div class="receipt-logo-icon"><i class="fas fa-building"></i></div>
                <div>
                    <div style="font-weight: 800; font-size: 15px;">{{ $societyName }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $societyAddress }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $societyEmail }} | {{ $societyPhone }}</div>
                </div>
            </div>
            <span class="receipt-paid-badge" style="{{ $isPaid ? '' : 'background: #fee2e2; color: #dc2626;' }}">{{ strtoupper($payment->statusLabel()) }}</span>
        </div>

        <div class="receipt-title">PAYMENT RECEIPT</div>

        {{-- Meta grid --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px 24px; margin-bottom: 20px;">
            <div>
                <div class="cell-sub">Receipt No.</div>
                <div style="color: var(--primary); font-weight: 700;">{{ $payment->receipt_number }}</div>
            </div>
            <div>
                <div class="cell-sub">Receipt Date</div>
                <div style="font-weight: 600;">{{ $payment->receipt_date?->format('d M Y, h:i A') }}</div>
            </div>
            <div>
                <div class="cell-sub">Received From</div>
                <div style="font-weight: 700;">{{ $payment->member_name }} @if($payment->unit_type)({{ $payment->unit_type }})@endif</div>
                <div style="font-size: 12px; color: var(--text-secondary);">{{ $payment->unit_label }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">{{ $memberMobile }} @if($memberEmail) | {{ $memberEmail }}@endif</div>
            </div>
            <div>
                <div class="cell-sub">Payment Mode</div>
                <div style="font-weight: 600;">
                    @if($payment->payment_mode)<i class="fas {{ $payment->paymentModeIcon() }}" style="color: var(--text-muted);"></i> {{ $payment->paymentModeLabel() }}@else &mdash; @endif
                </div>
                <div class="cell-sub" style="margin-top: 10px;">Transaction / UTR No.</div>
                <div style="font-weight: 600;">{{ $payment->transaction_utr ?: '—' }}</div>
            </div>
        </div>

        {{-- Particulars --}}
        <table class="particulars-table">
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th>Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $payment->bill_type }} Charges ({{ $payment->bill_period }})</td>
                    <td>{{ number_format((float) $payment->total_due, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>{{ number_format((float) $payment->discount, 2) }}</td>
                </tr>
                <tr>
                    <td>Fine / Penalty</td>
                    <td>{{ number_format((float) $payment->fine_penalty, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Amount</td>
                    <td>{{ number_format((float) $payment->total_due, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td style="color: var(--success); font-weight: 700;">{{ number_format((float) $payment->paid_amount, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td>Balance Amount</td>
                    <td>&#8377; {{ number_format((float) $payment->balance_due, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Footer --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 24px;">
            <div>
                <div style="color: var(--success); font-weight: 700; font-size: 13px;">Thank you for your payment.</div>
                <div class="note-text" style="max-width: 320px;">This is a computer generated receipt and does not require signature.</div>
            </div>
            <div class="receipt-stamp">
                <div>GREEN MEADOWS<br>SOCIETY</div>
                <div style="margin-top: 4px;">NAVI<br>MUMBAI</div>
            </div>
        </div>
    </div>
</div>
