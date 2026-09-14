@extends('superadmin.layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Record Payment</h1>
            <p class="page-subtitle">Apply a received payment to an open invoice.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.payments') }}">Payments</a>
        <span class="breadcrumb-separator">/</span>
        <span>Record</span>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<form action="{{ route('superadmin.billing.payments.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Invoice</div>
                <div class="form-group">
                    <label class="form-label">Open Invoice <span class="required">*</span></label>
                    <select name="invoice_id" class="form-control" required>
                        <option value="">Select invoice</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}" {{ (string) old('invoice_id', $selected) === (string) $inv->id ? 'selected' : '' }}>
                                {{ $inv->invoice_number }} — {{ $inv->society?->name }} · outstanding &#8377;{{ number_format($inv->outstanding_amount, 2) }} · due {{ $inv->due_date->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Date <span class="required">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-credit-card"></i> Payment</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Mode <span class="required">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            @foreach($paymentModes->isEmpty() ? ['Bank Transfer', 'UPI', 'Cheque', 'Cash', 'Card'] : $paymentModes as $mode)
                                <option value="{{ $mode }}" {{ old('payment_method') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reference / Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" value="{{ old('transaction_id') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
        <a href="{{ route('superadmin.billing.payments') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Record Payment</button>
    </div>
</form>
@endsection
