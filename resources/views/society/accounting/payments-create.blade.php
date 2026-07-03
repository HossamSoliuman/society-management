@extends('society.layouts.app')

@section('title', 'Add Payment')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Payment</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.payments') }}">Payments</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Payment</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.accounting.payments.store') }}">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="content-grid">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Payment Details</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="date" value="{{ old('date', '2025-05-30') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payee <span class="required">*</span></label>
                            <input type="text" name="payee" value="{{ old('payee') }}" class="form-control" placeholder="Enter payee / vendor name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mode <span class="required">*</span></label>
                            <select name="mode" class="form-control" required>
                                <option value="">Select Mode</option>
                                @foreach($paymentModes as $mode)
                                    <option value="{{ $mode }}" {{ old('mode') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Paid From Account <span class="required">*</span></label>
                            <select name="account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ (string) old('account_id') === (string) $account->id ? 'selected' : '' }}>{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="Txn / cheque no.">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose</label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" class="form-control" placeholder="What is this payment for?">
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                        <a href="{{ route('society.accounting.payments') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Payment</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span><strong>Note</strong> — The payment reduces the balance of the selected account and is posted as a payment transaction.</span>
            </div>
        </div>
    </div>
</form>
@endsection
