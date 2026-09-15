@extends('society.layouts.app')

@section('title', 'Generate Bills')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Generate Bills</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.bills.index') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <span>Generate Bills</span>
            </div>
        </div>
        <a href="{{ route('society.billing.bills.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Bills</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('society.billing.bills.generate.store') }}">
    @csrf
    <div class="content-grid">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--primary-light); color: var(--primary);"><i class="fas fa-calendar"></i></div>
                        <div><div class="title" style="font-size: 16px;">Billing Period</div></div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Bill Month <span class="required">*</span></label>
                            <input type="text" name="bill_month" class="form-control" value="{{ old('bill_month', $defaultMonth) }}" placeholder="e.g. June 2025" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bill Date <span class="required">*</span></label>
                            <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', $defaultBillDate) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date <span class="required">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $defaultDueDate) }}" required>
                        </div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Bill Cycle</label>
                            <input type="text" name="bill_cycle" class="form-control" value="{{ old('bill_cycle') }}" placeholder="Defaults to bill month">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Billing Type</label>
                            <input type="text" name="billing_type" class="form-control" value="{{ old('billing_type', $settings->default_bill_type) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tower / Building</label>
                            <select name="tower" class="form-control">
                                <option value="">All occupied units</option>
                                @foreach($towers as $tower)
                                    <option value="{{ $tower }}" {{ old('tower') === $tower ? 'selected' : '' }}>{{ $tower }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-list-check"></i></div>
                        <div><div class="title" style="font-size: 16px;">Charge Heads</div></div>
                    </div>
                    @php($selected = collect(old('charge_heads', $chargeHeads->where('type', 'recurring')->pluck('id')->all()))->map(fn ($v) => (int) $v))
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th style="width: 40px;"></th><th>Charge Head</th><th>Category</th><th>Calculation</th><th style="text-align: right;">Amount (&#8377;)</th></tr>
                            </thead>
                            <tbody>
                                @forelse($chargeHeads as $head)
                                    <tr>
                                        <td><input type="checkbox" name="charge_heads[]" value="{{ $head->id }}" @checked($selected->contains($head->id))></td>
                                        <td><strong>{{ $head->name }}</strong><div style="font-size: 11px; color: var(--text-muted);">{{ $head->description }}</div></td>
                                        <td><span class="badge {{ $head->categoryBadgeClass() }}">{{ $head->categoryLabel() }}</span></td>
                                        <td>{{ $head->calculationTypeLabel() }}</td>
                                        <td style="text-align: right;">{{ number_format($head->default_amount, 2) }}{{ $head->calculation_type === 'per_sqft' ? ' / sqft' : '' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="empty-state"><div class="empty-state-title">No active charge heads</div><div class="empty-state-text"><a href="{{ route('society.billing.settings.charge-heads') }}">Add charge heads</a> before generating bills.</div></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-paper-plane"></i></div>
                        <div><div class="title" style="font-size: 16px;">Delivery</div></div>
                    </div>
                    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" name="send_email" value="1" @checked(old('send_email', $settings->auto_email_bill))> Email bill (PDF) to members</label>
                        <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" name="send_sms" value="1" @checked(old('send_sms', $settings->auto_sms_bill))> Send SMS</label>
                    </div>
                    <div class="form-group" style="margin-top: 12px;">
                        <label class="form-label">Notes (printed on every bill)</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Preview</div>
                    @if($preview)
                        <div class="review-row"><span class="review-label">Bills to create</span><span class="review-value" style="font-weight: 700;">{{ $preview['units'] }}</span></div>
                        <div class="review-row"><span class="review-label">Already billed (skipped)</span><span class="review-value">{{ $preview['skipped'] }}</span></div>
                        <div class="review-row"><span class="review-label">Charge heads</span><span class="review-value">{{ $preview['lines']->count() }}</span></div>
                        <div class="review-row"><span class="review-label">Total demand</span><span class="review-value" style="font-weight: 700; color: var(--primary);">{{ format_inr($preview['total']) }}</span></div>
                        <div style="display: grid; gap: 8px; margin-top: 16px;">
                            <button type="submit" name="confirm" value="1" class="btn btn-primary" {{ $preview['units'] === 0 ? 'disabled' : '' }}><i class="fas fa-check"></i> Confirm &amp; Generate {{ $preview['units'] }} Bill(s)</button>
                            <button type="submit" name="confirm" value="0" class="btn btn-secondary"><i class="fas fa-rotate"></i> Recalculate Preview</button>
                        </div>
                    @else
                        <p style="font-size: 13px; color: var(--text-secondary);">One bill is created per occupied unit ({{ $units->count() }} units). Units already billed for the month are skipped.</p>
                        <button type="submit" name="confirm" value="0" class="btn btn-primary" style="width: 100%;"><i class="fas fa-eye"></i> Preview</button>
                    @endif
                </div>
            </div>
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span>Active taxes are applied to every bill. Previous dues are carried forward when "Include previous dues" is on in Bill Settings.</span>
            </div>
        </div>
    </div>
</form>
@endsection
