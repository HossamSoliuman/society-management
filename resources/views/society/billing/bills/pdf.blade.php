<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $bill['number'] }}</title>
    @php
        $primary = $design->primary_color ?: '#FF6A00';
        $text = $design->text_color ?: '#374151';
    @endphp
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 11px; color: {{ $text }}; margin: 0; padding: 24px; }
        .header { width: 100%; border-bottom: 3px solid {{ $primary }}; padding-bottom: 12px; margin-bottom: 16px; }
        .header td { vertical-align: top; }
        .society { font-size: 18px; font-weight: bold; color: {{ $primary }}; }
        .muted { color: #6b7280; }
        .title { font-size: 16px; font-weight: bold; text-align: right; color: {{ $primary }}; }
        .meta { text-align: right; line-height: 1.6; }
        .parties { width: 100%; margin-bottom: 14px; }
        .parties td { vertical-align: top; width: 50%; padding: 8px; background: #f8fafc; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 4px; }
        table.charges { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.charges th { background: {{ $primary }}; color: #fff; text-align: left; padding: 7px 8px; font-size: 10px; }
        table.charges td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; }
        table.charges td.num, table.charges th.num { text-align: right; }
        table.totals { width: 45%; margin-left: 55%; border-collapse: collapse; }
        table.totals td { padding: 4px 8px; }
        table.totals td.num { text-align: right; }
        table.totals tr.grand td { font-weight: bold; font-size: 13px; border-top: 2px solid {{ $primary }}; color: {{ $primary }}; }
        .words { margin: 10px 0; font-style: italic; }
        .pay { width: 100%; margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .pay td { vertical-align: top; }
        .terms { margin-top: 16px; font-size: 10px; color: #6b7280; }
        .terms ul { margin: 4px 0 0 14px; padding: 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; background: #f1f5f9; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="society">{{ strtoupper($bill['society_name']) }}</div>
                @if($design->show_address)<div class="muted">{{ $bill['society_address'] }}</div>@endif
                @if($design->show_contact)
                    <div class="muted">Phone: {{ $bill['society_phone'] }} &nbsp; Email: {{ $bill['society_email'] }} @if($bill['society_website']) &nbsp; {{ $bill['society_website'] }} @endif</div>
                @endif
                @if($design->show_gstin && $bill['gst_number'])<div class="muted">GSTIN: {{ $bill['gst_number'] }}</div>@endif
            </td>
            <td>
                <div class="title">MAINTENANCE BILL</div>
                <div class="meta">
                    Bill No.: <strong>{{ $bill['number'] }}</strong><br>
                    Bill Date: {{ $bill['date'] }}<br>
                    Due Date: {{ $bill['due_date'] }}<br>
                    <span class="badge">{{ ucfirst($bill['status']) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <div class="label">Bill To</div>
                <div><strong>{{ $bill['to_name'] }}</strong></div>
                <div class="muted">{{ $bill['to_flat'] }}</div>
                <div class="muted">{{ $bill['to_society'] }}</div>
            </td>
            <td>
                <div class="label">Bill Details</div>
                <div>Bill Month: {{ $bill['month'] }}</div>
                <div>Bill Type: {{ $bill['type'] }}</div>
                <div>Bill Cycle: {{ $bill['cycle'] }}</div>
            </td>
        </tr>
    </table>

    <table class="charges">
        <thead>
            <tr><th style="width: 30px;">#</th><th>Charge Head</th><th>Description</th><th class="num">Amount (Rs.)</th></tr>
        </thead>
        <tbody>
            @foreach($bill['items'] as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="muted">{{ $item['description'] }}</td>
                    <td class="num">{{ number_format($item['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Sub Total</td><td class="num">{{ number_format($bill['subtotal'], 2) }}</td></tr>
        @if($bill['discount'] > 0)<tr><td>Discount</td><td class="num">- {{ number_format($bill['discount'], 2) }}</td></tr>@endif
        @if($bill['tax'] > 0)<tr><td>Tax</td><td class="num">{{ number_format($bill['tax'], 2) }}</td></tr>@endif
        @if($bill['late_fee'] > 0)<tr><td>Late Fee</td><td class="num">{{ number_format($bill['late_fee'], 2) }}</td></tr>@endif
        @if($design->show_previous_balance || $bill['previous_dues'] > 0)<tr><td>Previous Dues</td><td class="num">{{ number_format($bill['previous_dues'], 2) }}</td></tr>@endif
        <tr class="grand"><td>Total Payable</td><td class="num">Rs. {{ number_format($bill['total_payable'], 2) }}</td></tr>
        @if($bill['collected'] > 0)
            <tr><td>Paid</td><td class="num">{{ number_format($bill['collected'], 2) }}</td></tr>
            <tr><td><strong>Balance Due</strong></td><td class="num"><strong>{{ number_format($bill['outstanding'], 2) }}</strong></td></tr>
        @endif
    </table>

    <div class="words">Amount in words: {{ $bill['amount_in_words'] }}</div>

    <table class="pay">
        <tr>
            <td>
                <strong>Payment Options</strong><br>
                @if($bill['bank_name'])
                    Bank Transfer: {{ $bill['bank_name'] }}
                    @if($bill['account_number']) · A/c {{ $bill['account_number'] }} @endif
                    @if($bill['ifsc_code']) · IFSC {{ $bill['ifsc_code'] }} @endif<br>
                @endif
                @if($bill['upi_id'] && $design->show_qr)UPI: {{ $bill['upi_id'] }}<br>@endif
                Cash / Cheque at the society office.
            </td>
        </tr>
    </table>

    @if($design->show_thank_you)
        <div class="muted" style="margin-top: 10px;">{{ $bill['footer_note'] ?: 'Thank you for being a valued member.' }}</div>
    @endif

    @if($design->show_terms)
        <div class="terms">
            <strong>Terms &amp; Conditions</strong>
            @if($bill['terms'])
                <div style="white-space: pre-line;">{{ $bill['terms'] }}</div>
            @else
                <ul>
                    <li>Please pay before the due date to avoid late fee.</li>
                    <li>This is a system generated bill and does not require a signature.</li>
                    <li>For any queries, please contact the society office.</li>
                </ul>
            @endif
        </div>
    @endif
</body>
</html>
