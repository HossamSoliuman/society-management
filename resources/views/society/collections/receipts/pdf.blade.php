<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $payment->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 11px; color: #374151; margin: 0; padding: 24px; }
        .header { width: 100%; border-bottom: 3px solid #16a34a; padding-bottom: 10px; margin-bottom: 14px; }
        .header td { vertical-align: top; }
        .society { font-size: 18px; font-weight: bold; color: #16a34a; }
        .muted { color: #6b7280; }
        .title { font-size: 16px; font-weight: bold; text-align: right; color: #16a34a; }
        .meta { text-align: right; line-height: 1.6; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; background: {{ $payment->status === 'paid' ? '#dcfce7' : '#fee2e2' }}; color: {{ $payment->status === 'paid' ? '#166534' : '#991b1b' }}; }
        table.info { width: 100%; margin-bottom: 14px; }
        table.info td { vertical-align: top; width: 50%; padding: 8px; background: #f8fafc; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 4px; }
        table.lines { width: 100%; border-collapse: collapse; }
        table.lines th { background: #16a34a; color: #fff; text-align: left; padding: 7px 8px; font-size: 10px; }
        table.lines td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; }
        .num { text-align: right; }
        tr.grand td { font-weight: bold; font-size: 13px; color: #16a34a; border-top: 2px solid #16a34a; }
        .words { margin: 10px 0; font-style: italic; }
        .footer { margin-top: 18px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="society">{{ strtoupper($society->name) }}</div>
                <div class="muted">{{ collect([$society->address_line_1, $society->city, $society->state, $society->pincode])->filter()->implode(', ') }}</div>
                <div class="muted">{{ $society->primary_mobile }} @if($society->primary_email) · {{ $society->primary_email }} @endif</div>
            </td>
            <td>
                <div class="title">PAYMENT RECEIPT</div>
                <div class="meta">
                    Receipt No.: <strong>{{ $payment->receipt_number }}</strong><br>
                    Date: {{ $payment->receipt_date?->format('d M Y, h:i A') }}<br>
                    <span class="badge">{{ strtoupper($payment->statusLabel()) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info">
        <tr>
            <td>
                <div class="label">Received From</div>
                <div><strong>{{ $payment->member_name ?: '—' }}</strong></div>
                <div class="muted">{{ collect([$payment->flat_number, $payment->unit_label])->filter()->implode(', ') }}</div>
                @if($payment->member_mobile)<div class="muted">{{ $payment->member_mobile }}</div>@endif
            </td>
            <td>
                <div class="label">Payment Details</div>
                <div>Mode: {{ $payment->paymentModeLabel() }}</div>
                @if($payment->transaction_utr)<div>Reference: {{ $payment->transaction_utr }}</div>@endif
                @if($payment->reference_no)<div>Ref No.: {{ $payment->reference_no }}</div>@endif
                @if($payment->collected_by)<div>Collected by: {{ $payment->collected_by }}</div>@endif
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead><tr><th>Description</th><th class="num">Amount (Rs.)</th></tr></thead>
        <tbody>
            <tr><td>{{ $payment->bill_type }} @if($payment->bill_period)({{ $payment->bill_period }})@endif @if($payment->maintenanceBill)· {{ $payment->maintenanceBill->bill_number }}@endif</td><td class="num">{{ number_format((float) $payment->total_due, 2) }}</td></tr>
            @if((float) $payment->discount > 0)<tr><td>Discount</td><td class="num">- {{ number_format((float) $payment->discount, 2) }}</td></tr>@endif
            @if((float) $payment->fine_penalty > 0)<tr><td>Fine / Penalty</td><td class="num">{{ number_format((float) $payment->fine_penalty, 2) }}</td></tr>@endif
            <tr class="grand"><td>Amount Received</td><td class="num">Rs. {{ number_format((float) $payment->paid_amount, 2) }}</td></tr>
            <tr><td>Balance Due</td><td class="num">{{ number_format((float) $payment->balance_due, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="words">Amount in words: {{ amount_in_words_inr($payment->paid_amount) }}</div>

    @if($payment->notes)<div class="muted">Notes: {{ $payment->notes }}</div>@endif

    <div class="footer">This is a system generated receipt and does not require a signature. Thank you for your payment.</div>
</body>
</html>
