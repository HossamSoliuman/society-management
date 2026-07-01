@extends('society.layouts.app')

@section('title', 'Bill Design')

@php
    $templates = [
        'modern' => 'Modern',
        'classic' => 'Classic',
        'compact' => 'Compact',
        'minimal' => 'Minimal',
    ];

    // Demo bill used for the live preview (reused shape by Phase 2B).
    $items = $chargeHeads->map(fn ($ch) => [
        'name' => $ch->name,
        'description' => $ch->description,
        'amount' => (float) $ch->default_amount,
    ])->all();
    $subtotal = collect($items)->sum('amount');
    $bill = [
        'number' => 'MB-2025-000156',
        'date' => '30 May 2025',
        'due_date' => '15 Jun 2025',
        'to_name' => 'Mr. Ramesh Sharma',
        'to_flat' => 'A-101, Tower A, 1st Floor',
        'month' => 'June 2025',
        'type' => 'Monthly Maintenance',
        'cycle' => 'June 2025',
        'items' => $items,
        'subtotal' => $subtotal,
        'total' => $subtotal,
        'total_payable' => $subtotal,
        'upi_id' => 'greenview@sbi',
    ];
    $design = $settings;
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Bill Design</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.settings.general') }}">Bill Settings</a>
                <span class="breadcrumb-separator">/</span>
                <span>Bill Design</span>
            </div>
        </div>
        <button type="submit" form="billDesignForm" class="btn btn-primary"><i class="fas fa-save"></i> Save Design</button>
    </div>
</div>

@include('society.billing.settings._tabs')

<div style="display: grid; grid-template-columns: 42% 1fr; gap: 20px; align-items: start;" class="bill-design-grid">
    {{-- Left config column --}}
    <form method="POST" action="{{ route('society.billing.settings.design.update') }}" id="billDesignForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="template" id="tpl_input" value="{{ $settings->template }}">

        <div class="card">
            <div class="card-body">
                {{-- Select Template --}}
                <div class="card-title" style="font-size: 16px; margin-bottom: 14px;">Select Template</div>
                <div class="template-grid">
                    @foreach($templates as $key => $label)
                        <div class="template-thumb {{ $settings->template === $key ? 'active' : '' }}" data-template="{{ $key }}" onclick="selectTemplate('{{ $key }}', this)">
                            <div class="thumb-check"><i class="fas fa-check"></i></div>
                            <div class="thumb-preview">
                                <div class="bar accent"></div>
                                <div class="bar"></div>
                                <div class="bar"></div>
                                <div class="bar" style="width: 70%;"></div>
                            </div>
                            <div class="thumb-label">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Company / Society Details --}}
                <div class="card-title" style="font-size: 16px; margin: 24px 0 14px;">Company / Society Details</div>
                <div class="form-group">
                    <label class="form-label">Logo</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div class="logo-preview">
                            <span style="color: #16a34a; font-weight: 700; letter-spacing: 1px;"><i class="fas fa-building"></i> {{ strtoupper($settings->society_name ?: 'Green View Residency') }}</span>
                        </div>
                        <button type="button" class="btn btn-outline-secondary"><i class="fas fa-upload"></i> Change Logo</button>
                    </div>
                    <div class="form-text">Recommended size: 250 x 80px</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Society Name</label>
                    <input type="text" name="society_name" class="form-control" value="{{ $settings->society_name }}" oninput="setPreview('society_name', this.value.toUpperCase())">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2" oninput="setPreview('address', this.value)">{{ $settings->address }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $settings->phone }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control" value="{{ $settings->email }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Website (Optional)</label>
                    <input type="text" name="website" class="form-control" value="{{ $settings->website }}">
                </div>

                {{-- Bill Header Options --}}
                <div class="card-title" style="font-size: 16px; margin: 24px 0 14px;">Bill Header Options</div>
                <div class="form-row">
                    <label class="form-check" style="padding: 6px 0;">
                        <input type="checkbox" name="show_logo" value="1" class="form-check-input" {{ $settings->show_logo ? 'checked' : '' }} onchange="togglePreview('logo', this.checked)">
                        <span class="form-check-label">Show Society Logo</span>
                    </label>
                    <label class="form-check" style="padding: 6px 0;">
                        <input type="checkbox" name="show_contact" value="1" class="form-check-input" {{ $settings->show_contact ? 'checked' : '' }} onchange="togglePreview('contact', this.checked)">
                        <span class="form-check-label">Show Contact Details</span>
                    </label>
                </div>
                <div class="form-row">
                    <label class="form-check" style="padding: 6px 0;">
                        <input type="checkbox" name="show_address" value="1" class="form-check-input" {{ $settings->show_address ? 'checked' : '' }} onchange="togglePreview('address', this.checked)">
                        <span class="form-check-label">Show Society Address</span>
                    </label>
                    <label class="form-check" style="padding: 6px 0;">
                        <input type="checkbox" name="show_gstin" value="1" class="form-check-input" {{ $settings->show_gstin ? 'checked' : '' }}>
                        <span class="form-check-label">Show GSTIN</span>
                    </label>
                </div>

                {{-- Colors & Theme --}}
                <div class="card-title" style="font-size: 16px; margin: 24px 0 14px;">Colors &amp; Theme</div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Primary Color</label>
                        <div class="color-field">
                            <input type="color" value="{{ $settings->primary_color }}" oninput="syncColor('primary', this.value)">
                            <input type="text" name="primary_color" id="primary_hex" value="{{ $settings->primary_color }}" oninput="syncColor('primary', this.value)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Secondary Color</label>
                        <div class="color-field">
                            <input type="color" value="{{ $settings->secondary_color }}" oninput="document.getElementById('secondary_hex').value = this.value;">
                            <input type="text" name="secondary_color" id="secondary_hex" value="{{ $settings->secondary_color }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <div class="color-field">
                            <input type="color" value="{{ $settings->text_color }}" oninput="syncColor('text', this.value)">
                            <input type="text" name="text_color" id="text_hex" value="{{ $settings->text_color }}" oninput="syncColor('text', this.value)">
                        </div>
                    </div>
                </div>

                {{-- Other Options --}}
                <div class="card-title" style="font-size: 16px; margin: 24px 0 14px;">Other Options</div>
                <div class="settings-2col" style="gap: 0 24px;">
                    <div class="setting-toggle-row">
                        <div class="setting-text"><div class="label">Show Thank You Note</div></div>
                        <label class="toggle-switch"><input type="checkbox" name="show_thank_you" value="1" {{ $settings->show_thank_you ? 'checked' : '' }} onchange="togglePreview('thank_you', this.checked)"><span class="toggle-slider"></span></label>
                    </div>
                    <div class="setting-toggle-row">
                        <div class="setting-text"><div class="label">Show Payment QR Code</div></div>
                        <label class="toggle-switch"><input type="checkbox" name="show_qr" value="1" {{ $settings->show_qr ? 'checked' : '' }} onchange="togglePreview('qr', this.checked)"><span class="toggle-slider"></span></label>
                    </div>
                    <div class="setting-toggle-row">
                        <div class="setting-text"><div class="label">Show Footer Note</div></div>
                        <label class="toggle-switch"><input type="checkbox" name="show_footer_note" value="1" {{ $settings->show_footer_note ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                    </div>
                    <div class="setting-toggle-row">
                        <div class="setting-text"><div class="label">Show Terms &amp; Conditions</div></div>
                        <label class="toggle-switch"><input type="checkbox" name="show_terms" value="1" {{ $settings->show_terms ? 'checked' : '' }} onchange="togglePreview('terms', this.checked)"><span class="toggle-slider"></span></label>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Right preview column --}}
    <div class="card">
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div class="card-title" style="font-size: 16px;">Preview</div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="fas fa-download"></i> Download Sample</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()"><i class="fas fa-rotate"></i> Reset to Default</button>
                </div>
            </div>
            <div class="receipt-preview" id="billPreview">
                @include('society.billing._bill-template', ['design' => $design, 'bill' => $bill])
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectTemplate(key, el) {
        document.querySelectorAll('.template-thumb').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('tpl_input').value = key;
    }
    function setPreview(field, value) {
        const node = document.querySelector('#billPreview [data-bill="' + field + '"]');
        if (node) { node.textContent = value; }
    }
    function togglePreview(field, show) {
        const node = document.querySelector('#billPreview [data-bill="' + field + '"]');
        if (node) { node.style.display = show ? '' : 'none'; }
    }
    function syncColor(kind, value) {
        const doc = document.querySelector('#billPreview .bill-doc');
        if (kind === 'primary') {
            document.getElementById('primary_hex').value = value;
            if (doc) {
                doc.style.setProperty('--bill-primary', value);
                doc.style.setProperty('--bill-primary-light', value + '1f');
            }
        } else if (kind === 'text') {
            document.getElementById('text_hex').value = value;
            if (doc) { doc.style.setProperty('--bill-text', value); }
        }
    }
</script>
@endpush
@endsection
