@extends('superadmin.layouts.app')

@section('title', 'Issue Refund')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Issue Refund</h1>
            <p class="page-subtitle">Refund all or part of a received payment.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.refunds') }}">Refunds</a>
        <span class="breadcrumb-separator">/</span>
        <span>Issue</span>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<form action="{{ route('superadmin.billing.refunds.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-receipt"></i> Payment</div>
                <div class="form-group">
                    <label class="form-label">Payment <span class="required">*</span></label>
                    <select name="payment_id" class="form-control" required>
                        <option value="">Select payment</option>
                        @foreach($payments as $payment)
                            <option value="{{ $payment->id }}" {{ (string) old('payment_id', $selected) === (string) $payment->id ? 'selected' : '' }}>
                                {{ $payment->receipt_number }} — {{ $payment->society?->name }} · &#8377;{{ number_format($payment->amount, 2) }} · refundable &#8377;{{ number_format($payment->refundableAmount(), 2) }}
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
                        <label class="form-label">Refund Date <span class="required">*</span></label>
                        <input type="date" name="refund_date" class="form-control" value="{{ old('refund_date', now()->toDateString()) }}" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-rotate-left"></i> Refund</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Method <span class="required">*</span></label>
                        <select name="refund_method" class="form-control" required>
                            @foreach($paymentModes->isEmpty() ? ['Bank Transfer', 'UPI', 'Cheque', 'Cash'] : $paymentModes as $mode)
                                <option value="{{ $mode }}" {{ old('refund_method') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
        <a href="{{ route('superadmin.billing.refunds') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Issue Refund</button>
    </div>
</form>
@endsection
