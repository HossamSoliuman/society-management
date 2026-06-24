@extends('superadmin.layouts.app')

@section('title', 'Add New Subscription Plan')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Subscription Plan</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.plans') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Add New Plan</span>
    </div>
</div>

<form action="{{ route('superadmin.subscription.plans.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-tag"></i> Plan Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Plan Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter plan name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plan Type <span class="required">*</span></label>
                        <select name="plan_type" class="form-control" required>
                            <option value="">Select plan type</option>
                            <option value="basic">Basic</option>
                            <option value="standard">Standard</option>
                            <option value="premium">Premium</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                        <div class="form-text">Select the category of this plan</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Plan Code <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Enter plan code (e.g. PREMIUM)" required>
                        <div class="form-text">Unique code for internal reference</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" name="amount" class="form-control" placeholder="Enter amount" step="0.01" min="0" required>
                        </div>
                        <div class="form-text">Set the price for this plan</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Plan Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter plan description"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plan Duration <span class="required">*</span></label>
                        <select name="plan_duration" class="form-control" required>
                            <option value="">Select duration</option>
                            <option value="1_month">1 Month</option>
                            <option value="3_months">3 Months</option>
                            <option value="6_months">6 Months</option>
                            <option value="1_year" selected>1 Year</option>
                            <option value="2_years">2 Years</option>
                        </select>
                        <div class="form-text">Duration for which this plan is valid</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-sliders-h"></i> Plan Settings</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing Cycle <span class="required">*</span></label>
                        <select name="billing_cycle" class="form-control" required>
                            <option value="">Select billing cycle</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="yearly" selected>Yearly</option>
                        </select>
                        <div class="form-text">How often this plan will be billed</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Trial Period (Optional)</label>
                        <input type="number" name="trial_period_days" class="form-control" placeholder="0" value="0" min="0">
                        <div class="form-text">Enter 0 for no trial period</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plan Badge (Optional)</label>
                        <input type="text" name="badge" class="form-control" placeholder="e.g. Popular, Best Value">
                        <div class="form-text">Displayed as a badge on plan</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Plan Priority</label>
                        <input type="number" name="priority" class="form-control" placeholder="0" value="0" min="0">
                        <div class="form-text">Higher number shows first in listings</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plan Color (Optional)</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="color" name="color" class="form-control" value="#E84B1E" style="width: 50px; padding: 4px; height: 40px;">
                            <input type="text" class="form-control" value="#E84B1E" readonly>
                        </div>
                        <div class="form-text">Color used for plan badge/label</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div class="section-title" style="margin: 0;"><i class="fas fa-th-large"></i> Module Access & Permissions</div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px;">
                    <span>Select All</span>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Enable or disable access to modules in this plan. Permissions for each module will be applied automatically.</div>

            <div class="modules-grid">
                @php $modules = ['Dashboard', 'User Management', 'Visitor Management', 'Complaint Management', 'Facility Management', 'Revenue & Billing', 'Reports', 'Notifications', 'Activity Logs', 'Document Management']; @endphp
                @php $moduleIcons = ['fa-chart-line', 'fa-users', 'fa-walking', 'fa-exclamation-circle', 'fa-couch', 'fa-rupee-sign', 'fa-chart-bar', 'fa-bell', 'fa-clipboard-list', 'fa-file-alt']; @endphp
                @php $moduleColors = ['info', 'success', 'purple', 'orange', 'teal', 'warning', 'pink', 'info', 'success', 'purple']; @endphp
                @foreach($modules as $idx => $module)
                <div class="module-item">
                    <div class="module-icon {{ $moduleColors[$idx] }}-light" style="background: var(--{{ $moduleColors[$idx] }}-light); color: var(--{{ $moduleColors[$idx] }});">
                        <i class="fas {{ $moduleIcons[$idx] }}"></i>
                    </div>
                    <div class="module-info" style="flex: 1;">
                        <h4>{{ $module }}</h4>
                        <p>{{ ['View dashboard & analytics', 'Manage members & users', 'Manage visitors & gate entries', 'Manage complaints & tickets', 'Manage society facilities & amenities', 'Manage invoices & payments', 'View & export reports', 'Send & manage notifications', 'View activity & audit logs', 'Manage documents & files'][$idx] }}</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="modules[]" value="{{ strtolower(str_replace(' ', '_', $module)) }}" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                @endforeach
            </div>

            <div class="info-box" style="margin-top: 20px;">
                <i class="fas fa-info-circle"></i>
                <span>Module access is plan based. Users subscribed to this plan will have access only to the enabled modules.</span>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-bottom: 40px;">
        <a href="{{ route('superadmin.subscription.plans') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Subscription Plan</button>
    </div>
</form>
@endsection
