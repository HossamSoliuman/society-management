@extends('society.layouts.app')

@section('title', 'Record Payment')

@php
    $modes = [
        'cash' => ['label' => 'Cash', 'icon' => 'fa-money-bill-wave'],
        'upi' => ['label' => 'UPI', 'icon' => 'fa-mobile-screen'],
        'card' => ['label' => 'Card', 'icon' => 'fa-credit-card'],
        'net_banking' => ['label' => 'Net Banking', 'icon' => 'fa-building-columns'],
        'cheque' => ['label' => 'Cheque', 'icon' => 'fa-money-check'],
        'other' => ['label' => 'Other', 'icon' => 'fa-ellipsis'],
    ];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Record Payment</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Collections</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.collections.index') }}">Payment Collection</a>
                <span class="breadcrumb-separator">/</span>
                <span>Record Payment</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.collections.store') }}" enctype="multipart/form-data" id="paymentForm">
    @csrf
    <input type="hidden" name="print" id="printFlag" value="0">
    <input type="hidden" name="member_name" id="memberNameInput">
    <input type="hidden" name="flat_number" id="flatNumberInput">
    <input type="hidden" name="unit_label" id="unitLabelInput">

    <div class="content-grid" style="grid-template-columns: 1fr 360px;">
        <div>
            <div class="card">
                <div class="card-body">
                    {{-- 1. Select Member / Unit --}}
                    <div class="section-title" style="font-size: 15px;">1. Select Member / Unit</div>
                    <div class="form-row" style="margin-bottom: 24px;">
                        <div class="form-group">
                            <label class="form-label">Select Member <span style="color: var(--danger);">*</span></label>
                            <select name="member_id" id="memberSelect" class="form-control" required>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}"
                                        data-name="{{ $member->name }}"
                                        data-type="{{ $member->typeLabel() }}"
                                        data-phone="{{ $member->mobile }}"
                                        data-email="{{ $member->email }}"
                                        data-flat="{{ $member->flat_unit }}"
                                        data-wing="{{ $member->tower_wing }}">
                                        {{ $member->name }} ({{ $member->typeLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text" id="memberContact"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Unit / Flat <span style="color: var(--danger);">*</span></label>
                            <select name="unit_id" id="unitSelect" class="form-control" required>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        data-flat="{{ $unit->unit_number }}"
                                        data-type="{{ $unit->unit_type }}"
                                        data-label="{{ $unit->unit_number }} ({{ $unit->building }}, {{ $unit->wing }}, {{ $unit->floor }}, {{ $unit->unit_type }})"
                                        data-address="{{ $unit->unit_number }}, {{ $unit->building }}, {{ $unit->wing }}">
                                        {{ $unit->unit_number }} ({{ $unit->building }}, {{ $unit->wing }}, {{ $unit->floor }}, {{ $unit->unit_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 2. Payment Details --}}
                    <div class="section-title" style="font-size: 15px;">2. Payment Details</div>
                    <div class="form-row-3" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Bill Type <span style="color: var(--danger);">*</span></label>
                            <select name="bill_type" class="form-control"><option>Maintenance</option></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Bill Period <span style="color: var(--danger);">*</span></label>
                            <select name="bill_period" class="form-control">
                                <option value="May 2024">May 2024</option>
                                @foreach($billPeriods as $period)
                                    <option value="{{ $period }}">{{ $period }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" value="2024-05-31" class="form-control" style="background: var(--gray-50);">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Receipt Date <span style="color: var(--danger);">*</span></label>
                            <input type="date" name="receipt_date" value="2024-05-31" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row-3" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Total Due (&#8377;)</label>
                            <input type="number" step="0.01" name="total_due" id="totalDue" value="2850.00" class="form-control" style="background: var(--gray-50);" readonly>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Paid Amount (&#8377;) <span style="color: var(--danger);">*</span></label>
                            <input type="number" step="0.01" name="paid_amount" id="paidAmount" value="2850.00" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Discount (&#8377;)</label>
                            <input type="number" step="0.01" name="discount" id="discount" value="0.00" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Fine / Penalty (&#8377;)</label>
                            <input type="number" step="0.01" name="fine_penalty" id="finePenalty" value="0.00" class="form-control">
                        </div>
                    </div>

                    {{-- Payment Mode tiles --}}
                    <div class="form-group">
                        <label class="form-label">Payment Mode <span style="color: var(--danger);">*</span></label>
                        <div class="radio-card-group payment-mode-grid">
                            @foreach($modes as $val => $mode)
                                <label class="radio-card">
                                    <input type="radio" name="payment_mode" value="{{ $val }}" {{ $val === 'cash' ? 'checked' : '' }} required>
                                    <span class="radio-card-content"><i class="fas {{ $mode['icon'] }}"></i> {{ $mode['label'] }}</span>
                                    <span class="check-icon"><i class="fas fa-check"></i></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="Enter transaction / reference no.">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Transaction / UTR No.</label>
                            <input type="text" name="transaction_utr" class="form-control" placeholder="Enter UTR / transaction no.">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes (optional)"></textarea>
                    </div>

                    {{-- 3. Attachment --}}
                    <div class="section-title" style="font-size: 15px; margin-top: 8px;">3. Attachment (Optional)</div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Upload Receipt / Document</label>
                        <label class="file-upload" for="attachment">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <div class="file-upload-text">Click to upload or drag and drop</div>
                            <div class="file-upload-hint">JPG, PNG, PDF (Max. 5MB)</div>
                            <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                        </label>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 4px;">
                <a href="{{ route('society.collections.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-outline-primary" onclick="document.getElementById('printFlag').value='0'">Save Receipt</button>
                <button type="submit" class="btn btn-primary" onclick="document.getElementById('printFlag').value='1'">Save &amp; Print Receipt</button>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            {{-- Member / Unit Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Member / Unit Information</div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div class="avatar" style="width: 48px; height: 48px;">
                            <img id="railAvatar" src="https://ui-avatars.com/api/?name=Member&background=E84B1E&color=fff" alt="">
                        </div>
                        <div>
                            <div style="font-weight: 700;" id="railName">—</div>
                            <div style="font-size: 12px; color: var(--text-muted);" id="railType">—</div>
                        </div>
                    </div>
                    <div class="summary-item"><span class="summary-label"><i class="fas fa-phone" style="width: 16px; color: var(--text-muted);"></i></span><span class="summary-value" id="railPhone">—</span></div>
                    <div class="summary-item"><span class="summary-label"><i class="fas fa-envelope" style="width: 16px; color: var(--text-muted);"></i></span><span class="summary-value" id="railEmail">—</span></div>
                    <div class="summary-item"><span class="summary-label"><i class="fas fa-location-dot" style="width: 16px; color: var(--text-muted);"></i></span><span class="summary-value" id="railAddress">—</span></div>
                    <div class="summary-item"><span class="summary-label"><i class="fas fa-bed" style="width: 16px; color: var(--text-muted);"></i></span><span class="summary-value" id="railUnitType">—</span></div>
                </div>
            </div>

            {{-- Payment Summary --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Payment Summary</div>
                    <div class="summary-item"><span class="summary-label">Bill Type</span><span class="summary-value">Maintenance</span></div>
                    <div class="summary-item"><span class="summary-label">Bill Period</span><span class="summary-value" id="sumPeriod">May 2024</span></div>
                    <div class="summary-item" style="border-top: 1px solid var(--border-color);"><span class="summary-label">Total Due</span><span class="summary-value" id="sumTotalDue">&#8377; 2,850.00</span></div>
                    <div class="summary-item"><span class="summary-label">Discount</span><span class="summary-value" style="color: var(--success);" id="sumDiscount">- &#8377; 0.00</span></div>
                    <div class="summary-item"><span class="summary-label">Fine / Penalty</span><span class="summary-value" style="color: var(--danger);" id="sumFine">+ &#8377; 0.00</span></div>
                    <div class="summary-item" style="border-top: 1px solid var(--border-color);"><span class="summary-label" style="font-weight: 700; color: var(--text-primary);">Amount Paid</span><span class="summary-value" style="color: var(--success); font-weight: 700;" id="sumPaid">&#8377; 2,850.00</span></div>
                    <div class="summary-item"><span class="summary-label" style="font-weight: 700; color: var(--text-primary);">Balance Due</span><span class="summary-value" style="font-weight: 700;" id="sumBalance">&#8377; 0.00</span></div>
                </div>
            </div>

            {{-- Confirmation note --}}
            <div class="info-box" style="background: #dcfce7; border-color: #86efac; color: #166534;">
                <i class="fas fa-circle-check"></i>
                <span>You are recording a payment. Please verify details before saving.</span>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const fmt = (n) => '&#8377; ' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const memberSelect = document.getElementById('memberSelect');
    const unitSelect = document.getElementById('unitSelect');

    function updateMemberRail() {
        const opt = memberSelect.options[memberSelect.selectedIndex];
        if (!opt) return;
        document.getElementById('railName').textContent = opt.dataset.name || '—';
        document.getElementById('railType').textContent = opt.dataset.type || '—';
        document.getElementById('railPhone').textContent = opt.dataset.phone || '—';
        document.getElementById('railEmail').textContent = opt.dataset.email || '—';
        document.getElementById('railAvatar').src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(opt.dataset.name || 'Member') + '&background=E84B1E&color=fff';
        document.getElementById('memberContact').textContent = (opt.dataset.phone || '') + ' | ' + (opt.dataset.email || '');
        document.getElementById('memberNameInput').value = opt.dataset.name || '';
    }

    function updateUnitRail() {
        const opt = unitSelect.options[unitSelect.selectedIndex];
        if (!opt) return;
        document.getElementById('railAddress').textContent = opt.dataset.address || '—';
        document.getElementById('railUnitType').textContent = opt.dataset.type || '—';
        document.getElementById('flatNumberInput').value = opt.dataset.flat || '';
        document.getElementById('unitLabelInput').value = opt.dataset.label || '';
    }

    function recompute() {
        const total = parseFloat(document.getElementById('totalDue').value) || 0;
        const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const fine = parseFloat(document.getElementById('finePenalty').value) || 0;
        const balance = Math.max(0, total - discount - paid);

        document.getElementById('sumTotalDue').innerHTML = fmt(total);
        document.getElementById('sumDiscount').innerHTML = '- ' + fmt(discount);
        document.getElementById('sumFine').innerHTML = '+ ' + fmt(fine);
        document.getElementById('sumPaid').innerHTML = fmt(paid);
        document.getElementById('sumBalance').innerHTML = fmt(balance);
        document.getElementById('sumPeriod').textContent = document.querySelector('[name=bill_period]').value;
    }

    memberSelect.addEventListener('change', updateMemberRail);
    unitSelect.addEventListener('change', updateUnitRail);
    ['totalDue', 'paidAmount', 'discount', 'finePenalty'].forEach((id) => {
        document.getElementById(id).addEventListener('input', recompute);
    });
    document.querySelector('[name=bill_period]').addEventListener('change', recompute);

    document.getElementById('attachment').addEventListener('change', function () {
        if (this.files.length) {
            this.closest('.file-upload').querySelector('.file-upload-text').textContent = this.files[0].name;
        }
    });

    updateMemberRail();
    updateUnitRail();
    recompute();
})();
</script>
@endpush
@endsection
