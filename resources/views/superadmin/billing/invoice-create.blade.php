@extends('superadmin.layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create Invoice</h1>
            <p class="page-subtitle">Raise a platform invoice against a society subscription.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.invoices') }}">Invoices</a>
        <span class="breadcrumb-separator">/</span>
        <span>Create</span>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<form action="{{ route('superadmin.billing.invoices.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-file-invoice"></i> Invoice Details</div>
                <div class="form-group">
                    <label class="form-label">Subscription <span class="required">*</span></label>
                    <select name="subscription_id" class="form-control" required>
                        <option value="">Select subscription</option>
                        @foreach($subscriptions as $sub)
                            <option value="{{ $sub->id }}" {{ (string) old('subscription_id', $selected) === (string) $sub->id ? 'selected' : '' }}>
                                {{ $sub->society?->name }} — {{ $sub->subscription_number }} · {{ $sub->plan?->name }} · &#8377;{{ number_format($sub->amount ?: ($sub->plan?->amount ?? 0)) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Leave amount blank to bill the subscription amount (or cost-per-flat × units when set).</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Amount (&#8377;)</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}" placeholder="Auto">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax (&#8377;)</label>
                        <input type="number" step="0.01" min="0" name="tax_amount" class="form-control" value="{{ old('tax_amount', 0) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Invoice Date <span class="required">*</span></label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Date <span class="required">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays($defaultDueDays)->toDateString()) }}" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-tag"></i> Classification</div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="Defaults to the plan name">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
        <a href="{{ route('superadmin.billing.invoices') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Raise Invoice</button>
    </div>
</form>
@endsection
