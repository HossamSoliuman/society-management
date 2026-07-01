@extends('society.layouts.app')

@section('title', 'Create Maintenance Bill')

@php
    $memberOptions = $members->map(fn ($m) => [
        'id' => $m->id,
        'name' => $m->name,
        'flat' => $m->flat_unit,
        'tower' => $m->tower_wing,
        'phone' => $m->mobile,
        'email' => $m->email,
        'type' => $m->typeLabel(),
    ])->values();

    $subtotal = collect($defaultLines)->sum('amount');
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create Maintenance Bill</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.bills.index') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <span>Create Bill</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('society.billing.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Bills</a>
            <button type="button" class="btn btn-primary" onclick="previewBill()"><i class="fas fa-eye"></i> Preview Bill</button>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        Please correct the highlighted fields and try again.
    </div>
@endif

<form method="POST" action="{{ route('society.billing.bills.store') }}" id="billForm">
    @csrf
    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 20px; align-items: start;">
        {{-- Left column --}}
        <div>
            {{-- Card A: Bill Details --}}
            <div class="card">
                <div class="card-body">
                    <div class="card-title" style="font-size: 16px; margin-bottom: 20px;">Bill Details</div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Bill Month <span class="required">*</span></label>
                            <div class="input-icon-group">
                                <input type="text" name="bill_month" class="form-control" value="{{ old('bill_month', 'June 2025') }}" required>
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bill Date <span class="required">*</span></label>
                            <div class="input-icon-group">
                                <input type="text" name="bill_date" class="form-control" value="{{ old('bill_date', '30 May 2025') }}" required>
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date <span class="required">*</span></label>
                            <div class="input-icon-group">
                                <input type="text" name="due_date" class="form-control" value="{{ old('due_date', '15 Jun 2025') }}" required>
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Billing Type <span class="required">*</span></label>
                            <select name="billing_type" class="form-control" required>
                                @foreach(['Monthly Maintenance', 'Quarterly Maintenance', 'Special Assessment', 'Ad-hoc Charges'] as $type)
                                    <option value="{{ $type }}" {{ old('billing_type', 'Monthly Maintenance') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bill Cycle</label>
                            <select name="bill_cycle" class="form-control">
                                @foreach(['June 2025', 'May 2025', 'April 2025', 'July 2025'] as $cycle)
                                    <option value="{{ $cycle }}" {{ old('bill_cycle', 'June 2025') === $cycle ? 'selected' : '' }}>{{ $cycle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes / Remarks</label>
                            <textarea name="notes" class="form-control" rows="1" placeholder="Enter any notes (optional)">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card B: Select Member / Unit --}}
            <div class="card">
                <div class="card-body">
                    <div class="card-title" style="font-size: 16px; margin-bottom: 20px;">Select Member / Unit</div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 12px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Tower / Wing</label>
                            <select class="form-control" id="filterTower">
                                <option value="">All Towers</option>
                                @foreach($towers as $tower)
                                    <option value="{{ $tower }}">{{ $tower }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Floor</label>
                            <select class="form-control" id="filterFloor">
                                <option value="">All Floors</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor }}">{{ $floor }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Flat / Unit</label>
                            <select class="form-control" id="filterUnit">
                                <option value="">Select Flat / Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->unit_number }}">{{ $unit->unit_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Member</label>
                            <select class="form-control" id="filterMember">
                                <option value="">Select Member</option>
                                @foreach($memberOptions as $m)
                                    <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="addSelectedMember()"><i class="fas fa-plus"></i> Add</button>
                    </div>

                    {{-- Selected member chip --}}
                    <input type="hidden" name="member_id" id="member_id" value="{{ old('member_id') }}">
                    <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id') }}">
                    <input type="hidden" name="member_name" id="member_name" value="{{ old('member_name', 'Mr. Ramesh Sharma') }}">
                    <input type="hidden" name="flat_number" id="flat_number" value="{{ old('flat_number', 'A-101') }}">
                    <input type="hidden" name="tower_wing" id="tower_wing" value="{{ old('tower_wing', 'Tower A') }}">
                    <input type="hidden" name="floor" id="floor" value="{{ old('floor', '1st Floor') }}">

                    <div class="member-chip-card" id="memberChip" style="margin-top: 16px;">
                        <div class="member-chip-icon"><i class="fas fa-building"></i></div>
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 40px; align-items: flex-start;">
                                <div>
                                    <div style="font-weight: 700; color: var(--text-primary);" id="chipFlat">A-101</div>
                                    <div style="font-size: 12px; color: var(--text-secondary);" id="chipLocation">Tower A, 1st Floor</div>
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: 700; color: var(--text-primary);" id="chipName">Mr. Ramesh Sharma</span>
                                        <span class="badge badge-orange" id="chipType">Owner</span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--primary); margin-top: 2px;" id="chipPhone">9876543210</div>
                                    <div style="font-size: 12px; color: var(--text-secondary);" id="chipEmail">ramesh.sharma@email.com</div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="action-btn delete" title="Remove" onclick="clearMember()"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>

            {{-- Card C: Maintenance Charges --}}
            <div class="card">
                <div class="card-body">
                    <div class="card-title" style="font-size: 16px; margin-bottom: 20px;">Maintenance Charges</div>
                    <div class="table-responsive">
                        <table class="data-table charge-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px; padding-left: 12px;">#</th>
                                    <th>Charge Head</th>
                                    <th>Description</th>
                                    <th style="text-align: right; width: 160px;">Amount (&#8377;)</th>
                                    <th style="width: 48px;"></th>
                                </tr>
                            </thead>
                            <tbody id="chargeRows">
                                @foreach($defaultLines as $i => $line)
                                    <tr class="charge-row">
                                        <td class="row-index" style="padding-left: 12px; color: var(--text-muted);">{{ $i + 1 }}</td>
                                        <td>
                                            <select class="form-control charge-head" onchange="onChargeHeadChange(this)">
                                                @foreach($chargeHeads as $ch)
                                                    <option value="{{ $ch->name }}" data-id="{{ $ch->id }}" data-amount="{{ $ch->default_amount }}" data-desc="{{ $ch->description }}" {{ $ch->name === $line['name'] ? 'selected' : '' }}>{{ $ch->name }}</option>
                                                @endforeach
                                                <option value="Others" {{ $line['name'] === 'Others' ? 'selected' : '' }}>Others</option>
                                            </select>
                                            <input type="hidden" class="charge-head-id" name="items[{{ $i }}][charge_head_id]" value="">
                                            <input type="hidden" class="charge-head-name" name="items[{{ $i }}][charge_head_name]" value="{{ $line['name'] }}">
                                        </td>
                                        <td><input type="text" class="form-control charge-desc" name="items[{{ $i }}][description]" value="{{ $line['description'] }}"></td>
                                        <td><input type="number" step="0.01" class="form-control charge-amount" name="items[{{ $i }}][amount]" value="{{ number_format($line['amount'], 2, '.', '') }}" style="text-align: right;" oninput="recalculate()"></td>
                                        <td><button type="button" class="action-btn delete" title="Remove" onclick="removeChargeRow(this)"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-link" onclick="addChargeRow()" style="margin-top: 12px;"><i class="fas fa-plus"></i> Add Another Charge</button>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div style="position: sticky; top: 20px;">
            {{-- Bill Summary --}}
            <div class="summary-panel" style="background: #fff; margin-bottom: 20px;">
                <div class="summary-panel-title">Bill Summary</div>
                <div id="summaryLines"></div>
                <div class="summary-item" style="border-top: 1px solid var(--border-color); margin-top: 4px;">
                    <span class="summary-label" style="font-weight: 600;">Sub Total</span>
                    <span class="summary-value" id="summarySubtotal">&#8377; {{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Discount</span>
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" step="0.01" name="discount" id="discountInput" value="0.00" class="form-control" style="width: 90px; text-align: right; padding: 6px 8px;" oninput="recalculate()">
                        <span class="summary-value" id="discountDisplay" style="min-width: 60px; text-align: right;">&#8377; 0.00</span>
                    </span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Late Fee</span>
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" step="0.01" name="late_fee" id="lateFeeInput" value="0.00" class="form-control" style="width: 90px; text-align: right; padding: 6px 8px;" oninput="recalculate()">
                        <span class="summary-value" id="lateFeeDisplay" style="min-width: 60px; text-align: right;">&#8377; 0.00</span>
                    </span>
                </div>
                <div style="border-top: 1px solid var(--border-color); margin-top: 8px; padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: var(--text-primary);">Total Amount</span>
                    <span style="font-weight: 700; font-size: 20px; color: var(--primary);" id="totalDisplay">&#8377; {{ number_format($subtotal, 2) }}</span>
                </div>
                <div style="font-size: 11px; color: var(--text-secondary); text-align: right; margin-top: 4px;" id="amountWords">({{ amount_in_words_inr($subtotal) }})</div>
            </div>

            {{-- Payment Information --}}
            <div class="summary-panel" style="background: #fff; margin-bottom: 20px;">
                <div class="summary-panel-title">Payment Information</div>
                <div class="form-group">
                    <label class="form-label">Collection Account <span class="required">*</span></label>
                    <select name="collection_account" class="form-control" required>
                        <option value="">Select Account</option>
                        @foreach($collectionAccounts as $account)
                            <option value="{{ $account }}" {{ old('collection_account') === $account ? 'selected' : '' }}>{{ $account }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-control">
                        <option value="">Select Mode</option>
                        @foreach($paymentModes as $mode)
                            <option value="{{ $mode }}" {{ old('payment_mode') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Reference No.</label>
                    <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="Enter reference no. (optional)">
                </div>
            </div>

            {{-- Send Bill To --}}
            <div class="summary-panel" style="background: #fff;">
                <div class="summary-panel-title">Send Bill To</div>
                <div class="form-check" style="margin-bottom: 10px;">
                    <input type="checkbox" class="form-check-input" name="send_email" id="send_email" value="1" checked>
                    <label class="form-check-label" for="send_email" style="display: flex; justify-content: space-between; width: 100%;">
                        <span style="font-weight: 600;">Email</span>
                        <span style="color: var(--text-secondary);" id="sendEmailValue">ramesh.sharma@email.com</span>
                    </label>
                </div>
                <div class="form-check" style="margin-bottom: 10px;">
                    <input type="checkbox" class="form-check-input" name="send_sms" id="send_sms" value="1" checked>
                    <label class="form-check-label" for="send_sms" style="display: flex; justify-content: space-between; width: 100%;">
                        <span style="font-weight: 600;">SMS</span>
                        <span style="color: var(--text-secondary);" id="sendSmsValue">9876543210</span>
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="send_whatsapp" id="send_whatsapp" value="1">
                    <label class="form-check-label" for="send_whatsapp" style="display: flex; justify-content: space-between; width: 100%;">
                        <span style="font-weight: 600;">WhatsApp</span>
                        <span style="color: var(--text-secondary);" id="sendWaValue">9876543210</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; padding-bottom: 24px;">
        <a href="{{ route('society.billing.bills.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice"></i> Save &amp; Generate Bill</button>
    </div>
</form>

@push('scripts')
<script>
    const MEMBERS = @json($memberOptions);
    const CHARGE_HEADS = @json($chargeHeads->map(fn ($c) => ['name' => $c->name, 'amount' => (float) $c->default_amount, 'desc' => $c->description])->values());

    function numberWords(num) {
        num = Math.floor(num);
        if (num === 0) return 'Zero';
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        const two = n => n < 20 ? ones[n] : (tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '')).trim();
        const three = n => {
            let s = '';
            if (n >= 100) { s = ones[Math.floor(n / 100)] + ' Hundred'; n %= 100; }
            if (n > 0) s = (s ? s + ' ' : '') + two(n);
            return s;
        };
        let parts = [];
        const crore = Math.floor(num / 10000000); num %= 10000000;
        if (crore > 0) parts.push(numberWords(crore) + ' Crore');
        const lakh = Math.floor(num / 100000); num %= 100000;
        if (lakh > 0) parts.push(two(lakh) + ' Lakh');
        const thousand = Math.floor(num / 1000); num %= 1000;
        if (thousand > 0) parts.push(two(thousand) + ' Thousand');
        if (num > 0) parts.push(three(num));
        return parts.join(' ').trim();
    }

    function fmt(n) {
        return '&#8377; ' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalculate() {
        let subtotal = 0;
        const lines = [];
        document.querySelectorAll('#chargeRows .charge-row').forEach(row => {
            const name = row.querySelector('.charge-head').value;
            const amount = parseFloat(row.querySelector('.charge-amount').value) || 0;
            row.querySelector('.charge-head-name').value = name;
            subtotal += amount;
            lines.push({ name, amount });
        });

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const lateFee = parseFloat(document.getElementById('lateFeeInput').value) || 0;
        const total = Math.max(0, subtotal - discount + lateFee);

        document.getElementById('summaryLines').innerHTML = lines.map(l =>
            `<div class="summary-item"><span class="summary-label">${l.name}</span><span class="summary-value">${fmt(l.amount)}</span></div>`
        ).join('');
        document.getElementById('summarySubtotal').innerHTML = fmt(subtotal);
        document.getElementById('discountDisplay').innerHTML = fmt(discount);
        document.getElementById('lateFeeDisplay').innerHTML = fmt(lateFee);
        document.getElementById('totalDisplay').innerHTML = fmt(total);
        document.getElementById('amountWords').textContent = '(Rupees ' + numberWords(total) + ' Only)';
    }

    let rowIndex = {{ count($defaultLines) }};
    function addChargeRow() {
        const tbody = document.getElementById('chargeRows');
        const options = CHARGE_HEADS.map(c => `<option value="${c.name}" data-amount="${c.amount}" data-desc="${c.desc ?? ''}">${c.name}</option>`).join('') + '<option value="Others">Others</option>';
        const tr = document.createElement('tr');
        tr.className = 'charge-row';
        tr.innerHTML = `
            <td class="row-index" style="padding-left: 12px; color: var(--text-muted);"></td>
            <td>
                <select class="form-control charge-head" onchange="onChargeHeadChange(this)">${options}</select>
                <input type="hidden" class="charge-head-id" name="items[${rowIndex}][charge_head_id]" value="">
                <input type="hidden" class="charge-head-name" name="items[${rowIndex}][charge_head_name]" value="">
            </td>
            <td><input type="text" class="form-control charge-desc" name="items[${rowIndex}][description]" value=""></td>
            <td><input type="number" step="0.01" class="form-control charge-amount" name="items[${rowIndex}][amount]" value="0.00" style="text-align: right;" oninput="recalculate()"></td>
            <td><button type="button" class="action-btn delete" title="Remove" onclick="removeChargeRow(this)"><i class="fas fa-trash"></i></button></td>`;
        tbody.appendChild(tr);
        rowIndex++;
        onChargeHeadChange(tr.querySelector('.charge-head'));
        renumberRows();
    }

    function removeChargeRow(btn) {
        const rows = document.querySelectorAll('#chargeRows .charge-row');
        if (rows.length <= 1) return;
        btn.closest('.charge-row').remove();
        renumberRows();
        recalculate();
    }

    function renumberRows() {
        document.querySelectorAll('#chargeRows .charge-row').forEach((row, i) => {
            row.querySelector('.row-index').textContent = i + 1;
        });
    }

    function onChargeHeadChange(select) {
        const opt = select.options[select.selectedIndex];
        const row = select.closest('.charge-row');
        row.querySelector('.charge-head-id').value = opt.dataset.id ?? '';
        row.querySelector('.charge-head-name').value = select.value;
        if (opt.dataset.desc) row.querySelector('.charge-desc').value = opt.dataset.desc;
        if (opt.dataset.amount) row.querySelector('.charge-amount').value = parseFloat(opt.dataset.amount).toFixed(2);
        recalculate();
    }

    function addSelectedMember() {
        const id = document.getElementById('filterMember').value;
        const member = MEMBERS.find(m => String(m.id) === String(id));
        if (!member) return;
        document.getElementById('member_id').value = member.id;
        document.getElementById('member_name').value = member.name;
        document.getElementById('flat_number').value = member.flat ?? '';
        document.getElementById('tower_wing').value = member.tower ?? '';
        document.getElementById('chipFlat').textContent = member.flat ?? '—';
        document.getElementById('chipLocation').textContent = member.tower ?? '';
        document.getElementById('chipName').textContent = member.name;
        document.getElementById('chipType').textContent = member.type ?? 'Owner';
        document.getElementById('chipPhone').textContent = member.phone ?? '';
        document.getElementById('chipEmail').textContent = member.email ?? '';
        document.getElementById('sendEmailValue').textContent = member.email ?? '';
        document.getElementById('sendSmsValue').textContent = member.phone ?? '';
        document.getElementById('sendWaValue').textContent = member.phone ?? '';
        document.getElementById('memberChip').style.display = 'flex';
    }

    function clearMember() {
        ['member_id', 'unit_id', 'member_name', 'flat_number', 'tower_wing', 'floor'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('memberChip').style.display = 'none';
    }

    function previewBill() {
        const win = window.open('', '_blank');
        win.document.write('<p style="font-family: sans-serif; padding: 24px;">Save the bill to view the full printable preview.</p>');
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#chargeRows .charge-head').forEach(s => {
            const row = s.closest('.charge-row');
            const opt = s.options[s.selectedIndex];
            row.querySelector('.charge-head-id').value = opt.dataset.id ?? '';
        });
        recalculate();
    });
</script>
@endpush
@endsection
