@extends('society.layouts.app')

@section('title', 'Bill Settings')

@php
    $sel = fn ($a, $b) => $a === $b ? 'selected' : '';
@endphp

@section('content')
<form method="POST" action="{{ route('society.billing.settings.general.update') }}">
    @csrf
    @method('PUT')

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Bill Settings</h1>
                <div class="breadcrumb" style="margin-top: 6px;">
                    <a href="{{ route('society.dashboard') }}">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                    <span class="breadcrumb-separator">/</span>
                    <span>Bill Settings</span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </div>

    @include('society.billing.settings._tabs')

    <div class="settings-grid">
        {{-- 1. General Settings --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">General Settings</div>
                <div class="card-subtitle">Configure basic maintenance billing preferences.</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Default Bill Type <span class="required">*</span></label>
                        <select name="default_bill_type" class="form-control">
                            @foreach(['Monthly Maintenance', 'Quarterly Maintenance', 'Half Yearly', 'Yearly', 'One-time'] as $opt)
                                <option value="{{ $opt }}" {{ $sel($settings->default_bill_type, $opt) }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Bill Cycle <span class="required">*</span></label>
                        <select name="default_bill_cycle" class="form-control">
                            @foreach(['Current Month', 'Previous Month', 'Next Month'] as $opt)
                                <option value="{{ $opt }}" {{ $sel($settings->default_bill_cycle, $opt) }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bill Generation Date <span class="required">*</span></label>
                        <select name="bill_generation_date" class="form-control">
                            @foreach(['1st of Every Month', '5th of Every Month', '10th of Every Month', 'Last Day of Month'] as $opt)
                                <option value="{{ $opt }}" {{ $sel($settings->bill_generation_date, $opt) }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Date (Days) <span class="required">*</span></label>
                        <input type="number" name="due_date_days" class="form-control" value="{{ $settings->due_date_days }}">
                        <div class="form-text">Days after bill generation date</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Grace Period (Days)</label>
                        <input type="number" name="grace_period_days" class="form-control" value="{{ $settings->grace_period_days }}">
                        <div class="form-text">Additional days before late fee</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Round Off Amount</label>
                        <select name="round_off" class="form-control">
                            @foreach(['Round to Nearest Rupee', 'Round Up', 'Round Down', 'No Rounding'] as $opt)
                                <option value="{{ $opt }}" {{ $sel($settings->round_off, $opt) }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <label class="form-check" style="margin-top: 8px;">
                    <input type="checkbox" name="allow_zero_amount_bills" value="1" class="form-check-input" {{ $settings->allow_zero_amount_bills ? 'checked' : '' }}>
                    <span class="form-check-label">Allow zero amount bills</span>
                </label>
                <div class="form-text" style="margin-left: 24px;">Generate bills even if total amount is zero</div>
            </div>
        </div>

        {{-- 2. Bill Calculation Settings --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Bill Calculation Settings</div>
                <div class="card-subtitle">Configure how bills are calculated.</div>

                <div class="form-group">
                    <label class="form-label">Calculation Method <span class="required">*</span></label>
                    <select name="calculation_method" class="form-control">
                        @foreach(['flat_based' => 'Flat Based', 'area_based' => 'Area Based', 'unit_based' => 'Unit Based', 'custom' => 'Custom'] as $val => $label)
                            <option value="{{ $val }}" {{ $sel($settings->calculation_method, $val) }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Include Sinking Fund</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="include_sinking_fund" value="1" {{ $settings->include_sinking_fund ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>
                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Include Reserve Fund</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="include_reserve_fund" value="1" {{ $settings->include_reserve_fund ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>
                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Adjust Advance Amount</div><div class="help">Auto adjust advance while generating bills</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="adjust_advance_amount" value="1" {{ $settings->adjust_advance_amount ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Minimum Bill Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">&#8377;</span>
                        <input type="number" step="0.01" name="minimum_bill_amount" class="form-control" value="{{ number_format((float) $settings->minimum_bill_amount, 2, '.', '') }}">
                    </div>
                    <div class="form-text">Minimum payable amount</div>
                </div>

                <label class="form-check" style="margin-top: 8px;">
                    <input type="checkbox" name="include_previous_dues" value="1" class="form-check-input" {{ $settings->include_previous_dues ? 'checked' : '' }}>
                    <span class="form-check-label">Include Previous Dues</span>
                </label>
                <div class="form-text" style="margin-left: 24px;">Add unpaid dues to current bill</div>
            </div>
        </div>

        {{-- 3. Display Settings --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Display Settings</div>
                <div class="card-subtitle">Choose what to show on the bill.</div>

                @php
                    $displayChecks = [
                        'show_society_details' => 'Show Society Details',
                        'show_member_details' => 'Show Member Details',
                        'show_flat_details' => 'Show Flat / Unit Details',
                        'show_bill_summary' => 'Show Bill Summary',
                        'show_previous_balance' => 'Show Previous Balance',
                        'show_payment_history' => 'Show Payment History',
                        'show_charge_head_description' => 'Show Charge Head Description',
                        'show_notes' => 'Show Notes / Remarks',
                        'show_payment_qr' => 'Show Payment QR Code',
                    ];
                @endphp
                @foreach($displayChecks as $name => $label)
                    <label class="form-check" style="padding: 6px 0;">
                        <input type="checkbox" name="{{ $name }}" value="1" class="form-check-input" {{ $settings->$name ? 'checked' : '' }}>
                        <span class="form-check-label">{{ $label }}</span>
                    </label>
                @endforeach

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Currency Format</label>
                    <select name="currency_format" class="form-control">
                        @foreach(['Indian Rupee (₹)', 'USD ($)', 'EUR (€)'] as $opt)
                            <option value="{{ $opt }}" {{ $sel($settings->currency_format, $opt) }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount Decimal Places</label>
                    <select name="amount_decimal_places" class="form-control">
                        <option value="0" {{ $sel((int) $settings->amount_decimal_places, 0) }}>0 (Example: 1,235)</option>
                        <option value="1" {{ $sel((int) $settings->amount_decimal_places, 1) }}>1 (Example: 1,234.5)</option>
                        <option value="2" {{ $sel((int) $settings->amount_decimal_places, 2) }}>2 (Example: 1,234.56)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 4. Other Settings --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Other Settings</div>
                <div class="card-subtitle">Miscellaneous bill related settings.</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Default Payment Mode</label>
                        <select name="default_payment_mode" class="form-control">
                            <option value="">Select Payment Mode</option>
                            @foreach(['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Card'] as $opt)
                                <option value="{{ $opt }}" {{ $sel($settings->default_payment_mode, $opt) }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Collection Account</label>
                        <select name="default_collection_account" class="form-control">
                            <option value="">Select Account</option>
                            @foreach(['Main Account', 'Sinking Fund Account', 'Reserve Account'] as $opt)
                                <option value="{{ $opt }}" {{ $sel($settings->default_collection_account, $opt) }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Allow Partial Payments</div><div class="help">Allow members to make partial payments</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="allow_partial_payments" value="1" {{ $settings->allow_partial_payments ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>
                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Auto Email Bill</div><div class="help">Send bill via email when generated</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="auto_email_bill" value="1" {{ $settings->auto_email_bill ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>
                <div class="setting-toggle-row">
                    <div class="setting-text"><div class="label">Auto SMS Bill</div><div class="help">Send bill via SMS when generated</div></div>
                    <label class="toggle-switch"><input type="checkbox" name="auto_sms_bill" value="1" {{ $settings->auto_sms_bill ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                </div>
            </div>
        </div>

        {{-- 5. Bill Numbering Format --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Bill Numbering Format</div>
                <div class="card-subtitle">Configure bill number format and sequence.</div>

                <div class="form-group">
                    <label class="form-label">Bill Number Prefix</label>
                    <input type="text" name="bill_number_prefix" class="form-control" value="{{ $settings->bill_number_prefix }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Bill Number Format <span class="required">*</span></label>
                    <select name="bill_number_format" class="form-control">
                        @foreach(['YYYYMMDD-XXXX', 'YYYY-XXXX', 'YYYYMM-XXXX', 'PREFIX-XXXX'] as $opt)
                            <option value="{{ $opt }}" {{ $sel($settings->bill_number_format, $opt) }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="info-box" style="margin-bottom: 16px;">
                    <i class="fas fa-circle-info"></i>
                    <span>Available Tags: YYYY (Year), MM (Month), DD (Day), XXXX (Sequence Number)</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Next Sequence Number</label>
                    <input type="text" name="next_sequence_number" class="form-control" value="{{ $settings->next_sequence_number }}">
                    <div class="form-text">This number will be used in the next bill</div>
                </div>
            </div>
        </div>

        {{-- 6. Default Notes --}}
        <div class="card">
            <div class="card-body">
                <div class="card-title" style="font-size: 16px;">Default Notes</div>
                <div class="card-subtitle">These notes will appear on every bill.</div>

                <div class="form-group">
                    <label class="form-label">Terms &amp; Conditions (Shown on Bill)</label>
                    <textarea name="terms_conditions" class="form-control" rows="4">{{ $settings->terms_conditions }}</textarea>
                    <div class="form-text">These terms will be printed at the bottom of the bill.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Footer Note (Optional)</label>
                    <textarea name="footer_note" class="form-control" rows="3">{{ $settings->footer_note }}</textarea>
                    <div class="form-text">This note will appear at the bottom of the bill.</div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
