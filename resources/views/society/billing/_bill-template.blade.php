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
                <div class="bill-doc-society-name" style="color: #16a34a;" data-bill="society_name">{{ strtoupper($bill['society_name'] ?? ($design->society_name ?: 'Society')) }}</div>
            </div>
            @if($design->show_address)
                <div class="bill-doc-contact" data-bill="address">{{ $bill['society_address'] ?? ($design->address ?: '') }}</div>
            @endif
            @if($design->show_contact)
                <div class="bill-doc-contact" data-bill="contact">
                    <i class="fas fa-phone"></i> {{ $bill['society_phone'] ?? ($design->phone ?: '') }}
                    &nbsp;&nbsp;<i class="fas fa-envelope"></i> {{ $bill['society_email'] ?? ($design->email ?: '') }}
                    @if(!empty($bill['society_website'] ?? $design->website))&nbsp;&nbsp;<i class="fas fa-globe"></i> {{ $bill['society_website'] ?? $design->website }}@endif
                </div>
            @endif
        </div>
        <div>
            <div class="bill-doc-title">MAINTENANCE BILL</div>
            <div class="bill-doc-meta">
                Bill No.&nbsp;: {{ $bill['number'] ?? '—' }}<br>
                Bill Date&nbsp;: {{ $bill['date'] ?? '—' }}<br>
                Due Date&nbsp;: {{ $bill['due_date'] ?? '—' }}
            </div>
        </div>
    </div>

    {{-- Bill To / Bill Details --}}
    <div class="bill-parties">
        <div>
            <div class="block-label">Bill To</div>
            <div style="font-weight: 600;">{{ $bill['to_name'] ?? '—' }}</div>
            <div style="color: var(--text-secondary);">{{ $bill['to_flat'] ?? '—' }}</div>
            <div style="color: var(--text-secondary);">{{ $bill['to_society'] ?? ($design->society_name ?: '') }}</div>
        </div>
        <div>
            <div class="block-label">Bill Details</div>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Bill Month</span><span>: {{ $bill['month'] ?? '—' }}</span></div>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Bill Type</span><span>: {{ $bill['type'] ?? '—' }}</span></div>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Bill Cycle</span><span>: {{ $bill['cycle'] ?? '—' }}</span></div>
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
        <div class="row"><span>Sub Total</span><span>{{ number_format($bill['subtotal'] ?? 0, 2) }}</span></div>
        <div class="row"><span>Discount</span><span>{{ number_format($bill['discount'] ?? 0, 2) }}</span></div>
        @if(($bill['tax'] ?? 0) > 0)<div class="row"><span>Tax</span><span>{{ number_format($bill['tax'], 2) }}</span></div>@endif
        <div class="row"><span>Late Fee</span><span>{{ number_format($bill['late_fee'] ?? 0, 2) }}</span></div>
        <div class="row"><span>Previous Dues</span><span>{{ number_format($bill['previous_dues'] ?? 0, 2) }}</span></div>
        <div class="row grand"><span>Total Payable</span><span>&#8377; {{ number_format($bill['total_payable'] ?? 0, 2) }}</span></div>
        @if(($bill['collected'] ?? 0) > 0)
            <div class="row"><span>Paid</span><span>{{ number_format($bill['collected'], 2) }}</span></div>
            <div class="row highlight"><span>Balance Due</span><span>&#8377; {{ number_format($bill['outstanding'] ?? 0, 2) }}</span></div>
        @endif
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
        @if($design->show_qr && !empty($bill['upi_id']))
            <div class="bill-qr-box" data-bill="qr">
                <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Scan &amp; Pay</div>
                <i class="fas fa-qrcode" style="font-size: 48px; color: var(--text-primary);"></i>
                <div style="margin-top: 4px;">UPI ID: {{ $bill['upi_id'] }}</div>
            </div>
        @endif
        @if(!empty($bill['bank_name']))
            <div style="font-size: 12px; color: var(--text-secondary);">
                <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Bank Transfer</div>
                {{ $bill['bank_name'] }}@if(!empty($bill['account_number'])) · A/c {{ $bill['account_number'] }}@endif @if(!empty($bill['ifsc_code']))· IFSC {{ $bill['ifsc_code'] }}@endif
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
