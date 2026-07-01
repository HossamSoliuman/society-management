@extends('society.layouts.app')

@section('title', 'Late Fee & Penalty')

@php
    $sel = fn ($a, $b) => $a === $b ? 'selected' : '';
@endphp

@section('content')
<form method="POST" action="{{ route('society.billing.settings.late-fee.update') }}">
    @csrf
    @method('PUT')

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Late Fee &amp; Penalty</h1>
                <div class="breadcrumb" style="margin-top: 6px;">
                    <a href="{{ route('society.dashboard') }}">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('society.billing.settings.general') }}">Bill Settings</a>
                    <span class="breadcrumb-separator">/</span>
                    <span>Late Fee &amp; Penalty</span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </div>

    @include('society.billing.settings._tabs')

    {{-- KPI cards --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        @include('society.partials.stat-card', ['icon' => 'fa-percent', 'iconVariant' => 'danger', 'label' => 'Late Fee Enabled', 'value' => $lateFee->enable_late_fee ? 'Yes' : 'No', 'trend' => 'Late fee is active for overdue bills', 'trendType' => 'muted'])
        @include('society.partials.stat-card', ['icon' => 'fa-calendar-days', 'iconVariant' => 'success', 'label' => 'Grace Period', 'value' => $lateFee->grace_period_days.' Days', 'trend' => 'After bill due date', 'trendType' => 'muted'])
        @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'warning', 'label' => 'Default Late Fee', 'value' => number_format($lateFee->late_fee_percent, 2).'%', 'trend' => 'Monthly on outstanding', 'trendType' => 'muted'])
        @include('society.partials.stat-card', ['icon' => 'fa-gem', 'iconVariant' => 'purple', 'label' => 'Interest on Arrears', 'value' => number_format($lateFee->interest_rate_annual, 2).'%', 'trend' => 'Annual interest rate', 'trendType' => 'muted'])
    </div>

    <div class="settings-3col-uneven">
        {{-- Late Fee Settings --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Late Fee Settings</div>
                <div class="card-subtitle">Configure late fee and penalty rules for overdue maintenance bills.</div>

                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Enable Late Fee</div><div class="help">Late fee will be applied on overdue bills</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="enable_late_fee" value="1" {{ $lateFee->enable_late_fee ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Grace Period (Days) <span class="required">*</span></label>
                    <input type="number" name="grace_period_days" id="lf_grace" class="form-control" value="{{ $lateFee->grace_period_days }}" oninput="recalcPreview()">
                    <div class="form-text">Number of days after due date before late fee is applied</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Late Fee Type</label>
                    <div style="display: flex; gap: 24px; padding: 6px 0;">
                        <label class="form-check">
                            <input type="radio" name="late_fee_type" value="percentage" class="form-check-input" {{ $lateFee->late_fee_type === 'percentage' ? 'checked' : '' }}>
                            <span class="form-check-label">Percentage of Outstanding Amount</span>
                        </label>
                        <label class="form-check">
                            <input type="radio" name="late_fee_type" value="flat" class="form-check-input" {{ $lateFee->late_fee_type === 'flat' ? 'checked' : '' }}>
                            <span class="form-check-label">Flat Amount</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Late Fee (%) <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="late_fee_percent" id="lf_percent" class="form-control" value="{{ number_format((float) $lateFee->late_fee_percent, 2, '.', '') }}" oninput="recalcPreview()">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Monthly late fee percentage on outstanding amount</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Maximum Late Fee Cap</label>
                    <input type="number" step="0.01" name="max_late_fee_cap" class="form-control" value="{{ $lateFee->max_late_fee_cap !== null ? number_format((float) $lateFee->max_late_fee_cap, 2, '.', '') : '' }}">
                    <div class="form-text">Maximum late fee amount per bill (leave blank for no limit)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Compounding</label>
                    <select name="compounding" class="form-control">
                        @foreach(['None', 'Monthly', 'Quarterly', 'Yearly'] as $opt)
                            <option value="{{ $opt }}" {{ $sel($lateFee->compounding, $opt) }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">How often late fee should be compounded</div>
                </div>

                <div class="info-box">
                    <i class="fas fa-circle-info"></i>
                    <span>Late fee will be calculated after grace period and added to outstanding amount.</span>
                </div>
            </div>
        </div>

        {{-- Penalty / Interest on Arrears --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Penalty / Interest on Arrears</div>
                <div class="card-subtitle">Configure penalty or interest for long pending dues.</div>

                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Enable Interest on Arrears</div><div class="help">Interest will be charged on long pending dues</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="enable_interest" value="1" {{ $lateFee->enable_interest ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Interest Calculation Type</label>
                    <select name="interest_calc_type" class="form-control">
                        <option value="simple" {{ $sel($lateFee->interest_calc_type, 'simple') }}>Simple Interest</option>
                        <option value="compound" {{ $sel($lateFee->interest_calc_type, 'compound') }}>Compound Interest</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Interest Rate (Annual %) <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="interest_rate_annual" class="form-control" value="{{ number_format((float) $lateFee->interest_rate_annual, 2, '.', '') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Annual interest rate on outstanding amount</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Apply Interest After (Days)</label>
                    <input type="number" name="apply_interest_after_days" class="form-control" value="{{ $lateFee->apply_interest_after_days }}">
                    <div class="form-text">Interest will apply if bill remains unpaid after this many days from due date</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Interest Calculation On</label>
                    <select name="interest_calc_on" class="form-control">
                        @foreach(['Outstanding Amount (Including Previous Dues)', 'Outstanding Amount Only'] as $opt)
                            <option value="{{ $opt }}" {{ $sel($lateFee->interest_calc_on, $opt) }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Round Off Interest</label>
                    <select name="round_off_interest" class="form-control">
                        @foreach(['Round to Nearest Rupee', 'Round Up', 'Round Down', 'No Rounding'] as $opt)
                            <option value="{{ $opt }}" {{ $sel($lateFee->round_off_interest, $opt) }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="info-box amber">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>Interest will be calculated daily and added to outstanding amount.</span>
                </div>
            </div>
        </div>

        {{-- Rules + Preview --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="card-title" style="font-size: 16px; margin-bottom: 12px;">Late Fee Rules</div>
                    <ul class="info-list check-primary">
                        <li><i class="fas fa-circle-check"></i> Late fee is applied after the grace period.</li>
                        <li><i class="fas fa-circle-check"></i> Late fee is calculated monthly on outstanding amount.</li>
                        <li><i class="fas fa-circle-check"></i> Late fee will be shown separately on the bill.</li>
                        <li><i class="fas fa-circle-check"></i> Maximum late fee cap will be applied if configured.</li>
                        <li><i class="fas fa-circle-check"></i> Interest will be charged on long pending dues.</li>
                        <li><i class="fas fa-circle-check"></i> All fees and interest are non-refundable.</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-title" style="font-size: 16px;">Preview Calculation</div>
                    <div class="card-subtitle">Example calculation for an overdue bill.</div>

                    @php
                        $previewRows = [
                            ['Bill Amount', '₹3,500.00'],
                            ['Due Date', '30 May 2025'],
                            ['Bill Date', '15 May 2025'],
                            ['Current Date', '02 Jun 2025'],
                            ['Overdue Days', '3 Days'],
                            ['Grace Period', $lateFee->grace_period_days.' Days'],
                        ];
                    @endphp
                    @foreach($previewRows as $row)
                        <div style="display: flex; justify-content: space-between; padding: 7px 0; font-size: 13px; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary);">{{ $row[0] }}</span>
                            <span style="font-weight: 600;" @if($row[0] === 'Grace Period') id="pv_grace" @endif>{{ $row[1] }}</span>
                        </div>
                    @endforeach
                    <div style="display: flex; justify-content: space-between; padding: 7px 0; font-size: 13px; border-bottom: 1px solid var(--border-color);">
                        <span style="color: var(--text-secondary);">Late Fee (<span id="pv_pct_label">{{ number_format($lateFee->late_fee_percent, 2) }}</span>%)<br><span class="note-text" id="pv_fee_note">(Not yet applicable)</span></span>
                        <span style="font-weight: 600;" id="pv_fee">₹0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 7px 0; font-size: 13px; border-bottom: 1px solid var(--border-color);">
                        <span style="color: var(--text-secondary);">Interest ({{ number_format($lateFee->interest_rate_annual, 2) }}% p.a.)<br><span class="note-text">(Not yet applicable)</span></span>
                        <span style="font-weight: 600;">₹0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px; margin-top: 8px; background: var(--primary-light); color: var(--primary); border-radius: var(--radius-sm); font-weight: 700;">
                        <span>Total Payable</span>
                        <span id="pv_total">₹3,500.00</span>
                    </div>
                    <div class="note-text" style="margin-top: 10px;">* This is just an example. Actual amounts may vary.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Exemptions --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title" style="font-size: 16px;">Exemptions</div>
            <div class="card-subtitle">Configure exemptions for late fees and interest.</div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Exempt Members</label>
                    <select name="exempt_members[]" class="form-control" multiple size="4">
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ in_array((string) $member->id, (array) $lateFee->exempt_members, true) ? 'selected' : '' }}>{{ $member->name }} ({{ $member->flat_unit }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Selected members will not be charged late fee or interest</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Exempt Charge Heads</label>
                    <select name="exempt_charge_heads[]" class="form-control" multiple size="4">
                        @foreach($chargeHeads as $ch)
                            <option value="{{ $ch->id }}" {{ in_array((string) $ch->id, (array) $lateFee->exempt_charge_heads, true) ? 'selected' : '' }}>{{ $ch->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Selected charge heads will be exempt from late fee and interest</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Exempt Bill Types</label>
                    <select name="exempt_bill_types[]" class="form-control" multiple size="4">
                        @foreach(['Monthly Maintenance', 'Quarterly Maintenance', 'Special Assessment', 'One-time'] as $bt)
                            <option value="{{ $bt }}" {{ in_array($bt, (array) $lateFee->exempt_bill_types, true) ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Selected bill types will be exempt from late fee and interest</div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function recalcPreview() {
        const billAmount = 3500;
        const overdueDays = 3;
        const grace = parseInt(document.getElementById('lf_grace').value || '0', 10);
        const pct = parseFloat(document.getElementById('lf_percent').value || '0');
        document.getElementById('pv_grace').textContent = grace + ' Days';
        document.getElementById('pv_pct_label').textContent = pct.toFixed(2);

        let fee = 0;
        let applicable = overdueDays > grace;
        if (applicable) {
            fee = billAmount * pct / 100;
        }
        document.getElementById('pv_fee').textContent = '₹' + fee.toFixed(2);
        document.getElementById('pv_fee_note').textContent = applicable ? '' : '(Not yet applicable)';
        document.getElementById('pv_total').textContent = '₹' + (billAmount + fee).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
</script>
@endpush
@endsection
