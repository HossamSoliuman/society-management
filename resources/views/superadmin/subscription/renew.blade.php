@extends('superadmin.layouts.app')

@section('title', 'Renew Subscription')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Renew / Upgrade Subscription</h1>
            <p class="page-subtitle">{{ $subscription->society->name }} · {{ $subscription->subscription_number }} · current plan {{ $subscription->plan->name }}</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.subscriptions') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Renew</span>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<form action="{{ route('superadmin.subscription.subscriptions.renew.store', $subscription) }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-rotate"></i> New Term</div>

                <div class="form-group">
                    <label class="form-label">Plan <span class="required">*</span></label>
                    <select name="plan_id" class="form-control" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ (string) old('plan_id', $subscription->plan_id) === (string) $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — &#8377;{{ number_format($plan->amount) }} / {{ str_replace('_', ' ', $plan->plan_duration) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Choosing a different plan upgrades the society; the current row is closed on the new start date.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $defaultStart->toDateString()) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $defaultEnd->toDateString()) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (&#8377;)</label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}" placeholder="Defaults to the plan amount">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-credit-card"></i> Payment (optional)</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">Select</option>
                            @foreach(['Bank Transfer', 'UPI', 'Cheque', 'Cash', 'Card'] as $method)
                                <option value="{{ $method }}" {{ old('payment_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
        <a href="{{ route('superadmin.subscription.subscriptions') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Renewal</button>
    </div>
</form>
@endsection
