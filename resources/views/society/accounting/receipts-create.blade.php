@extends('society.layouts.app')

@section('title', 'Add Receipt')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Receipt</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.receipts') }}">Receipts</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Receipt</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.accounting.receipts.store') }}">
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
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Receipt Details</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payer Name <span class="required">*</span></label>
                            <input type="text" name="payer_name" value="{{ old('payer_name') }}" class="form-control" placeholder="Enter payer name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Flat / Unit No.</label>
                            <input type="text" name="flat_no" value="{{ old('flat_no') }}" class="form-control" placeholder="e.g. A-101">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Receipt Type <span class="required">*</span></label>
                            <select name="receipt_type" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach($receiptTypes as $rt)
                                    <option value="{{ $rt['value'] }}" {{ old('receipt_type') === $rt['value'] ? 'selected' : '' }}>{{ $rt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mode of Payment <span class="required">*</span></label>
                            <select name="mode_of_payment" class="form-control" required>
                                <option value="">Select Mode</option>
                                @foreach($paymentModes as $mode)
                                    <option value="{{ $mode }}" {{ old('mode_of_payment') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="Txn / cheque no.">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deposit To Account <span class="required">*</span></label>
                            <select name="account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ (string) old('account_id') === (string) $account->id ? 'selected' : '' }}>{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Credit To (Income Account)</label>
                            <select name="income_account_id" class="form-control">
                                <option value="">Auto by receipt type</option>
                                @foreach($incomeAccounts as $account)
                                    <option value="{{ $account->id }}" {{ (string) old('income_account_id') === (string) $account->id ? 'selected' : '' }}>{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" value="{{ old('location') }}" class="form-control" placeholder="Tower / block (optional)" list="locationOptions">
                            <datalist id="locationOptions">
                                @foreach($locations as $location)
                                    <option value="{{ $location }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                        <a href="{{ route('society.accounting.receipts') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Receipt</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="info-box" style="background: var(--success-light); border-color: #86efac; color: #166534;">
                <i class="fas fa-circle-info"></i>
                <span><strong>Note</strong> — The receipt increases the balance of the selected account and is posted as a receipt transaction.</span>
            </div>
        </div>
    </div>
</form>
@endsection
