@php
    /**
     * Shared checkout card (society panel + member portal).
     *
     * @var \App\Models\PaymentGatewayOrder $order
     * @var array<string, mixed> $checkout   provider payload from PaymentGateway::createOrder()
     * @var string $returnUrl
     * @var string $webhookUrl
     * @var string|null $receiptUrl  overrides the society receipt link (member portal)
     */
    $provider = $checkout['provider'] ?? $order->provider;
    $receiptUrl = $receiptUrl ?? ($order->payment ? route('society.collections.receipts.show', $order->payment) : null);
@endphp
<div class="content-grid" style="grid-template-columns: 1fr 360px;">
    <div class="card">
        <div class="card-body">
            <div class="section-title" style="font-size: 15px;">Payment Summary</div>
            <div class="review-row"><span class="review-label">Order</span><span class="review-value">{{ $order->provider_order_id }}</span></div>
            @if($order->bill)
                <div class="review-row"><span class="review-label">Bill</span><span class="review-value">{{ $order->bill->bill_number }} · {{ $order->bill->bill_month }}</span></div>
            @endif
            <div class="review-row"><span class="review-label">Member</span><span class="review-value">{{ $order->member?->name ?? $order->bill?->member_name ?? '—' }}</span></div>
            <div class="review-row"><span class="review-label">Amount</span><span class="review-value" style="font-weight: 700; color: var(--primary);">{{ format_inr($order->amount) }}</span></div>
            <div class="review-row"><span class="review-label">Status</span><span class="review-value"><span class="badge {{ $order->isPaid() ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($order->status) }}</span></span></div>

            @if($order->isPaid())
                <div class="alert alert-success" style="margin-top: 16px;">
                    <i class="fas fa-check-circle"></i>
                    Payment received. Receipt {{ $order->payment?->receipt_number }} has been issued.
                </div>
                @if($receiptUrl)
                    <a href="{{ $receiptUrl }}" class="btn btn-secondary btn-sm"><i class="fas fa-receipt"></i> View receipt</a>
                @endif
            @elseif($provider === 'razorpay')
                <button type="button" id="rzp-button" class="btn btn-primary" style="margin-top: 16px;"><i class="fas fa-lock"></i> Pay {{ format_inr($order->amount) }} securely</button>
                @push('scripts')
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script>
                    (function () {
                        var options = {
                            key: @json($checkout['key'] ?? ''),
                            amount: @json($checkout['amount'] ?? 0),
                            currency: @json($checkout['currency'] ?? 'INR'),
                            order_id: @json($checkout['order_id'] ?? ''),
                            name: @json($order->society?->name),
                            description: @json($order->bill?->bill_number ?? 'Society dues'),
                            handler: function () { window.location = @json($returnUrl); }
                        };
                        document.getElementById('rzp-button').addEventListener('click', function () {
                            new Razorpay(options).open();
                        });
                    })();
                </script>
                @endpush
            @else
                <form method="POST" action="{{ $webhookUrl }}" style="margin-top: 16px;">
                    <input type="hidden" name="order_id" value="{{ $order->provider_order_id }}">
                    <input type="hidden" name="amount" value="{{ $order->amount }}">
                    <input type="hidden" name="status" value="paid">
                    <input type="hidden" name="method" value="upi">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-vial"></i> Simulate successful payment (test gateway)</button>
                </form>
                <div class="form-text" style="margin-top: 8px;">The "fake" gateway is active. Set PAYMENT_GATEWAY=razorpay with keys in .env for live payments.</div>
            @endif
        </div>
    </div>
    <div>
        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Once the gateway confirms the payment, a receipt is recorded, the bill is updated and the member is emailed automatically.</span>
        </div>
    </div>
</div>
