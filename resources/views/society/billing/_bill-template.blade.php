@php
    /**
     * Reusable printable maintenance-bill template.
     *
     * @var \App\Models\BillSetting $design  Design config (colours, show flags, society info)
     * @var array $bill                      Bill data (number, dates, parties, items, totals)
     */
    $primary = $design->primary_color ?: '#FF6A00';
    $text = $design->text_color ?: '#374151';
    $bill = $bill ?? [];
    $items = $bill['items'] ?? [];
@endphp
<div class="bill-doc" style="--bill-primary: {{ $primary }}; --bill-primary-light: {{ $primary }}1f; --bill-text: {{ $text }};">
    {{-- Header band --}}
    <div class="bill-doc-header">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                @if($design->show_logo)
                    <i class="fas fa-building" style="font-size: 30px; color: #16a34a;" data-bill="logo"></i>
                @endif
                <div class="bill-doc-society-name" style="color: #16a34a;" data-bill="society_name">{{ strtoupper($design->society_name ?: 'Green View Residency') }}</div>
            </div>
            @if($design->show_address)
                <div class="bill-doc-contact" data-bill="address">{{ $design->address ?: 'Plot No. 45, Sector 12, Navi Mumbai - 400705, Maharashtra, India' }}</div>
            @endif
            @if($design->show_contact)
                <div class="bill-doc-contact" data-bill="contact">
                    <i class="fas fa-phone"></i> {{ $design->phone ?: '+91 98765 43210' }}
                    &nbsp;&nbsp;<i class="fas fa-envelope"></i> {{ $design->email ?: 'greenview@society.in' }}
                    &nbsp;&nbsp;<i class="fas fa-globe"></i> {{ $design->website ?: 'www.greenviewresidency.in' }}
                </div>
            @endif
        </div>
        <div>
            <div class="bill-doc-title">MAINTENANCE BILL</div>
            <div class="bill-doc-meta">
                Bill No.&nbsp;: {{ $bill['number'] ?? 'MB-2025-000156' }}<br>
                Bill Date&nbsp;: {{ $bill['date'] ?? '30 May 2025' }}<br>
                Due Date&nbsp;: {{ $bill['due_date'] ?? '15 Jun 2025' }}
            </div>
        </div>
    </div>

    {{-- Bill To / Bill Details --}}
    <div class="bill-parties">
        <div>
            <div class="block-label">Bill To</div>
            <div style="font-weight: 600;">{{ $bill['to_name'] ?? 'Mr. Ramesh Sharma' }}</div>
            <div style="color: var(--text-secondary);">{{ $bill['to_flat'] ?? 'A-101, Tower A, 1st Floor' }}</div>
            <div style="color: var(--text-secondary);">{{ $bill['to_society'] ?? ($design->society_name ?: 'Green View Residency') }}</div>
        </div>
        <div>
            <div class="block-label">Bill Details</div>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Bill Month</span><span>: {{ $bill['month'] ?? 'June 2025' }}</span></div>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Bill Type</span><span>: {{ $bill['type'] ?? 'Monthly Maintenance' }}</span></div>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Bill Cycle</span><span>: {{ $bill['cycle'] ?? 'June 2025' }}</span></div>
        </div>
    </div>

    {{-- Charges table --}}
    <table class="bill-charges-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Charge Head</th>
                <th>Description</th>
                <th>Amount (&#8377;)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td style="color: var(--text-secondary);">{{ $item['description'] }}</td>
                    <td>{{ number_format($item['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="bill-totals">
        <div class="row"><span>Sub Total</span><span>{{ number_format($bill['subtotal'] ?? 3500, 2) }}</span></div>
        <div class="row"><span>Discount</span><span>{{ number_format($bill['discount'] ?? 0, 2) }}</span></div>
        <div class="row"><span>Late Fee</span><span>{{ number_format($bill['late_fee'] ?? 0, 2) }}</span></div>
        <div class="row highlight"><span>Total Amount</span><span>&#8377; {{ number_format($bill['total'] ?? 3500, 2) }}</span></div>
        <div class="row"><span>Previous Dues</span><span>{{ number_format($bill['previous_dues'] ?? 0, 2) }}</span></div>
        <div class="row grand"><span>Total Payable</span><span>&#8377; {{ number_format($bill['total_payable'] ?? 3500, 2) }}</span></div>
    </div>

    {{-- Payment methods + QR --}}
    <div class="bill-payment-row">
        <div style="flex: 1;">
            <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">Payment Methods</div>
            <div style="display: flex; gap: 18px;">
                <span><i class="fas fa-qrcode"></i> UPI / QR Code</span>
                <span><i class="fas fa-building-columns"></i> Bank Transfer</span>
                <span><i class="fas fa-money-bill"></i> Cash / Cheque</span>
            </div>
        </div>
        @if($design->show_qr)
            <div class="bill-qr-box" data-bill="qr">
                <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Scan &amp; Pay</div>
                <i class="fas fa-qrcode" style="font-size: 48px; color: var(--text-primary);"></i>
                <div style="margin-top: 4px;">UPI ID: {{ $bill['upi_id'] ?? 'greenview@sbi' }}</div>
            </div>
        @endif
    </div>

    @if($design->show_thank_you)
        <div style="margin-top: 14px; font-size: 12px; color: var(--text-secondary);" data-bill="thank_you">{{ $design->footer_note ?: 'Thank you for being a valued member.' }}</div>
    @endif

    @if($design->show_terms)
        <div class="bill-terms" data-bill="terms">
            <div style="font-weight: 700; color: var(--text-primary);">Terms &amp; Conditions</div>
            <ul>
                <li>Please pay before the due date to avoid late fee.</li>
                <li>This is a system generated bill and does not require signature.</li>
                <li>For any queries, please contact the society office.</li>
            </ul>
        </div>
    @endif
</div>
