@extends('superadmin.layouts.app')

@section('title', 'Add New Subscription')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Subscription</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.subscriptions') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Add New Subscription</span>
    </div>
</div>

<form action="{{ route('superadmin.subscription.subscriptions.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-file-contract"></i> Subscription Details</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Society <span class="required">*</span></label>
                        <select name="society_id" class="form-control" required>
                            <option value="">Select Society</option>
                            @foreach($societies as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Building / Wing <span class="required">*</span></label>
                        <select name="building_name" class="form-control" required>
                            <option value="">Select Building / Wing</option>
                            <option value="All">All Buildings</option>
                            <option value="Tower A">Tower A</option>
                            <option value="Tower B">Tower B</option>
                            <option value="Tower C">Tower C</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Subscription Plan <span class="required">*</span></label>
                        <select name="plan_id" class="form-control" required>
                            <option value="">Select Plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} - &#8377;{{ number_format($plan->amount) }}/{{ $plan->billing_cycle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing Cycle <span class="required">*</span></label>
                        <select name="billing_cycle" class="form-control" required>
                            <option value="">Select Billing Cycle</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Cost for Each Flat <span class="required">*</span></label>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>Enter the monthly cost applicable for each flat (same for all flats in this subscription).</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Cost per Flat (&#8377;) <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">&#8377;</span>
                        <input type="number" name="monthly_cost_per_flat" class="form-control" placeholder="Enter monthly cost per flat" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Start Date <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar" style="font-size: 12px;"></i></span>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar" style="font-size: 12px;"></i></span>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Additional Free Days</label>
                        <div class="input-group">
                            <input type="number" name="additional_free_days" class="form-control" placeholder="0" value="0" min="0">
                            <span class="input-group-text">days</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter description (optional)"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-info-circle"></i> Additional Information</div>
                    <div class="form-group">
                        <label class="form-label">Subscription ID (Optional)</label>
                        <input type="text" class="form-control" placeholder="Auto generated" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reference No. (Optional)</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Enter reference number">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter any additional notes"></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-credit-card"></i> Payment Information</div>
                    <div class="form-group">
                        <label class="form-label">Payment Method <span class="required">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Date <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar" style="font-size: 12px;"></i></span>
                            <input type="date" name="payment_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Proof (Optional)</label>
                        <div class="file-upload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div class="file-upload-text">Drag & drop file here or</div>
                            <button type="button" class="btn btn-outline-primary btn-sm">Browse Files</button>
                            <div class="file-upload-hint" style="margin-top: 8px;">Supports: JPG, PNG, PDF (Max 5MB)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-bottom: 40px;">
        <a href="{{ route('superadmin.subscription.subscriptions') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Subscription</button>
    </div>
</form>
@endsection
