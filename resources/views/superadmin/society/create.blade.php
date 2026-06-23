@extends('superadmin.layouts.app')

@section('title', 'Add Society')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Society</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.societies.index') }}">Society Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Add Society</span>
    </div>
</div>

<form action="{{ route('superadmin.societies.store') }}" method="POST" data-wizard>
    @csrf

    <div class="steps-wizard">
        <div class="step-item active">
            <div class="step-number">1</div>
            <div class="step-label">Basic Information</div>
        </div>
        <div class="step-item">
            <div class="step-number">2</div>
            <div class="step-label">Contact Details</div>
        </div>
        <div class="step-item">
            <div class="step-number">3</div>
            <div class="step-label">Officials Details</div>
        </div>
        <div class="step-item">
            <div class="step-number">4</div>
            <div class="step-label">Subscription &amp; Plan</div>
        </div>
        <div class="step-item">
            <div class="step-number">5</div>
            <div class="step-label">Review &amp; Submit</div>
        </div>
    </div>

    <div class="grid-3">
        <div style="grid-column: span 2;">

            {{-- Step 1: Basic Information --}}
            <div class="wizard-step" data-step="1">
                <div class="card">
                    <div class="card-body">
                        <div class="wizard-section">
                            <div class="wizard-section-title">Basic Information</div>
                            <div class="wizard-section-subtitle">Enter society basic details and registration information.</div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Society Name <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter society name" value="{{ old('name') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Society Registration Number <span class="required">*</span></label>
                                    <input type="text" name="registration_number" class="form-control" placeholder="Enter registration number" value="{{ old('registration_number') }}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Society Prefix <span class="required">*</span></label>
                                    <input type="text" name="prefix" class="form-control" placeholder="Enter unique prefix (e.g. ABC)" value="{{ old('prefix') }}" required maxlength="10">
                                    <div class="form-text">Prefix will be used for bills, receipts and transactions</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">PAN Number (Optional)</label>
                                    <input type="text" name="pan_number" class="form-control" placeholder="Enter PAN number" value="{{ old('pan_number') }}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Society Type <span class="required">*</span></label>
                                    <select name="society_type_id" class="form-control" required>
                                        <option value="">Select society type</option>
                                        @foreach($societyTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('society_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Registration Date <span class="required">*</span></label>
                                    <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date') }}">
                                </div>
                            </div>

                            <div class="card" style="background: var(--gray-50); margin-top: 20px;">
                                <div class="card-body">
                                    <div class="section-title" style="font-size: 14px;">
                                        <i class="fas fa-building"></i>
                                        Flats, Shops &amp; Offices Count
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Add total count of units in the society.</div>
                                    <div class="form-row-3">
                                        <div class="form-group">
                                            <label class="form-label">Flats / Apartments <span class="required">*</span></label>
                                            <input type="number" name="flats_count" class="form-control" placeholder="0" value="{{ old('flats_count', 0) }}" min="0" required>
                                            <div class="form-text">Total number of flats / apartments</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Shops / Retail Units <span class="required">*</span></label>
                                            <input type="number" name="shops_count" class="form-control" placeholder="0" value="{{ old('shops_count', 0) }}" min="0" required>
                                            <div class="form-text">Total number of shops / retail units</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Offices / Commercial Units <span class="required">*</span></label>
                                            <input type="number" name="offices_count" class="form-control" placeholder="0" value="{{ old('offices_count', 0) }}" min="0" required>
                                            <div class="form-text">Total number of offices / commercial units</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 24px 0;">

                        <div class="wizard-section">
                            <div class="wizard-section-title">Address Information</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Address Line 1 <span class="required">*</span></label>
                                    <input type="text" name="address_line_1" class="form-control" placeholder="Enter address line 1" value="{{ old('address_line_1') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" name="address_line_2" class="form-control" placeholder="Enter address line 2" value="{{ old('address_line_2') }}">
                                </div>
                            </div>
                            <div class="form-row-3">
                                <div class="form-group">
                                    <label class="form-label">City <span class="required">*</span></label>
                                    <input type="text" name="city" class="form-control" placeholder="Enter city" value="{{ old('city') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">State <span class="required">*</span></label>
                                    <select name="state" class="form-control" required>
                                        <option value="">Select state</option>
                                        <option value="Maharashtra" {{ old('state') == 'Maharashtra' ? 'selected' : '' }}>Maharashtra</option>
                                        <option value="Gujarat" {{ old('state') == 'Gujarat' ? 'selected' : '' }}>Gujarat</option>
                                        <option value="Karnataka" {{ old('state') == 'Karnataka' ? 'selected' : '' }}>Karnataka</option>
                                        <option value="Tamil Nadu" {{ old('state') == 'Tamil Nadu' ? 'selected' : '' }}>Tamil Nadu</option>
                                        <option value="Delhi" {{ old('state') == 'Delhi' ? 'selected' : '' }}>Delhi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pincode <span class="required">*</span></label>
                                    <input type="text" name="pincode" class="form-control" placeholder="Enter pincode" value="{{ old('pincode') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Contact Details --}}
            <div class="wizard-step" data-step="2">
                <div class="card">
                    <div class="card-body">
                        <div class="wizard-section">
                            <div class="wizard-section-title">Contact Details</div>
                            <div class="wizard-section-subtitle">Enter society contact and communication information.</div>

                            <div class="form-row-3">
                                <div class="form-group">
                                    <label class="form-label">Primary Email ID <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope" style="font-size: 12px;"></i></span>
                                        <input type="email" name="primary_email" class="form-control" placeholder="Enter primary email" value="{{ old('primary_email') }}" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Secondary Email ID (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope" style="font-size: 12px;"></i></span>
                                        <input type="email" name="secondary_email" class="form-control" placeholder="Enter secondary email" value="{{ old('secondary_email') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Primary Mobile Number <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone" style="font-size: 12px;"></i></span>
                                        <input type="text" name="primary_mobile" class="form-control" placeholder="Enter mobile number" value="{{ old('primary_mobile') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-3">
                                <div class="form-group">
                                    <label class="form-label">Alternate Mobile (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="font-size: 11px;">+91</span>
                                        <input type="text" name="alternate_mobile" class="form-control" placeholder="Enter alternate mobile" value="{{ old('alternate_mobile') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Landline Number (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="font-size: 11px;">+91</span>
                                        <input type="text" name="landline" class="form-control" placeholder="Enter landline number" value="{{ old('landline') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Website (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe" style="font-size: 12px;"></i></span>
                                        <input type="url" name="website" class="form-control" placeholder="Enter website URL" value="{{ old('website') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Officials Details --}}
            <div class="wizard-step" data-step="3">
                <div class="card">
                    <div class="card-body">
                        <div class="wizard-section">
                            <div class="wizard-section-title">Officials Contact Information</div>
                            <div class="wizard-section-subtitle">Enter the details of key officials of the society.</div>

                            <table class="officials-table">
                                <thead>
                                    <tr>
                                        <th>Designation</th>
                                        <th>Name <span class="required">*</span></th>
                                        <th>Mobile Number <span class="required">*</span></th>
                                        <th>Email (Optional)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-user-tie" style="color: var(--text-muted);"></i>
                                                <span>Chairman</span>
                                            </div>
                                        </td>
                                        <td><input type="text" name="chairman_name" class="form-control" placeholder="Enter chairman name" value="{{ old('chairman_name') }}"></td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text" style="font-size: 11px;">+91</span>
                                                <input type="text" name="chairman_mobile" class="form-control" placeholder="Enter mobile" value="{{ old('chairman_mobile') }}">
                                            </div>
                                        </td>
                                        <td><input type="email" name="chairman_email" class="form-control" placeholder="Enter email" value="{{ old('chairman_email') }}"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-user-tie" style="color: var(--text-muted);"></i>
                                                <span>Secretary</span>
                                            </div>
                                        </td>
                                        <td><input type="text" name="secretary_name" class="form-control" placeholder="Enter secretary name" value="{{ old('secretary_name') }}"></td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text" style="font-size: 11px;">+91</span>
                                                <input type="text" name="secretary_mobile" class="form-control" placeholder="Enter mobile" value="{{ old('secretary_mobile') }}">
                                            </div>
                                        </td>
                                        <td><input type="email" name="secretary_email" class="form-control" placeholder="Enter email" value="{{ old('secretary_email') }}"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-user-tie" style="color: var(--text-muted);"></i>
                                                <span>Treasurer</span>
                                            </div>
                                        </td>
                                        <td><input type="text" name="treasurer_name" class="form-control" placeholder="Enter treasurer name" value="{{ old('treasurer_name') }}"></td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text" style="font-size: 11px;">+91</span>
                                                <input type="text" name="treasurer_mobile" class="form-control" placeholder="Enter mobile" value="{{ old('treasurer_mobile') }}">
                                            </div>
                                        </td>
                                        <td><input type="email" name="treasurer_email" class="form-control" placeholder="Enter email" value="{{ old('treasurer_email') }}"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Subscription & Plan --}}
            <div class="wizard-step" data-step="4">
                <div class="card">
                    <div class="card-body">
                        <div class="wizard-section">
                            <div class="wizard-section-title">Subscription &amp; Plan</div>
                            <div class="wizard-section-subtitle">Choose a subscription plan and configure your society's subscription details.</div>

                            <div style="margin-bottom: 20px;">
                                <label class="form-label">1. Select Plan <span class="required">*</span></label>
                                <div class="plan-cards">
                                    @foreach($plans as $plan)
                                    <div class="plan-card {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}">
                                        <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" id="plan_{{ $plan->id }}" {{ old('subscription_plan_id') == $plan->id ? 'checked' : ($loop->first && !old('subscription_plan_id') ? 'checked' : '') }} style="position: absolute; opacity: 0;">
                                        <label for="plan_{{ $plan->id }}" style="cursor: pointer; display: block;">
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                                <div class="step-number" style="width: 24px; height: 24px; font-size: 11px; background: {{ old('subscription_plan_id') == $plan->id ? 'var(--primary)' : '#fff' }}; color: {{ old('subscription_plan_id') == $plan->id ? '#fff' : 'var(--gray-500)' }}; border-color: {{ old('subscription_plan_id') == $plan->id ? 'var(--primary)' : 'var(--border-color)' }};">&#10003;</div>
                                                <div class="plan-card-name">{{ $plan->name }}</div>
                                            </div>
                                            <div class="plan-card-price" style="color: {{ $plan->amount > 0 ? 'var(--primary)' : 'var(--purple)' }};">
                                                @if($plan->amount > 0)
                                                    &#8377; {{ number_format($plan->amount) }}
                                                @else
                                                    Custom Pricing
                                                @endif
                                                @if($plan->amount > 0)
                                                    <span>/ year</span>
                                                @endif
                                            </div>
                                            <div class="plan-card-units">Up to {{ $plan->max_units }} Units</div>
                                            <ul class="plan-card-features">
                                                <li><i class="fas fa-check"></i> Basic Features</li>
                                                <li><i class="fas fa-check"></i> Email Support</li>
                                                <li><i class="fas fa-check"></i> Regular Updates</li>
                                            </ul>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div style="margin-bottom: 8px;">
                                <label class="form-label">2. Subscription Details <span class="required">*</span></label>
                            </div>
                            <div class="form-row-3">
                                <div class="form-group">
                                    <label class="form-label">Subscription Start Date</label>
                                    <input type="date" name="subscription_start_date" class="form-control" value="{{ old('subscription_start_date', now()->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subscription End Date</label>
                                    <input type="date" name="subscription_end_date" class="form-control" value="{{ old('subscription_end_date', now()->addYear()->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Billing Cycle</label>
                                    <select name="billing_cycle" class="form-control">
                                        <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('billing_cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="half_yearly" {{ old('billing_cycle') == 'half_yearly' ? 'selected' : '' }}>Half Yearly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row-3">
                                <div class="form-group">
                                    <label class="form-label">Grace Period (Days)</label>
                                    <input type="number" name="grace_period_days" class="form-control" value="{{ old('grace_period_days', 15) }}" min="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Auto Renewal</label>
                                    <div style="display: flex; gap: 16px; padding-top: 10px;">
                                        <label class="form-check">
                                            <input type="radio" name="auto_renewal" value="1" {{ old('auto_renewal', '1') == '1' ? 'checked' : '' }}>
                                            <span>Yes</span>
                                        </label>
                                        <label class="form-check">
                                            <input type="radio" name="auto_renewal" value="0" {{ old('auto_renewal') == '0' ? 'checked' : '' }}>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Trial Period (Days)</label>
                                    <input type="number" name="trial_period_days" class="form-control" value="{{ old('trial_period_days', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Enter any notes or special instructions...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 5: Review & Submit --}}
            <div class="wizard-step" data-step="5">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-check-circle" style="color: var(--success);"></i> Review &amp; Submit</div>
                    </div>
                    <div class="card-body">
                        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">Please review all entered information before submitting.</p>
                        <div class="review-content">
                            <p style="color: var(--text-muted);">Loading review...</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right info panel --}}
        <div>
            <div class="card" style="position: sticky; top: 80px;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-info-circle" style="color: var(--primary);"></i> Prefix Information</div>
                </div>
                <div class="card-body">
                    <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">The prefix must be unique for each society.</p>
                    <ul class="info-list" style="margin-bottom: 16px;">
                        <li><i class="fas fa-check"></i> Maintenance Bill Numbers</li>
                        <li><i class="fas fa-check"></i> Receipt Numbers</li>
                        <li><i class="fas fa-check"></i> Transaction IDs</li>
                        <li><i class="fas fa-check"></i> Report References</li>
                    </ul>
                    <div style="background: var(--gray-50); border-radius: var(--radius); padding: 12px; font-size: 12px;">
                        <div style="font-weight: 600; margin-bottom: 8px;">Example:</div>
                        <div style="color: var(--text-secondary);">Prefix: <strong style="color: var(--primary);">GAN</strong></div>
                        <div style="color: var(--text-secondary);">Receipt: <strong>GAN-REC-0001</strong></div>
                        <div style="color: var(--text-secondary);">Bill No.: <strong>GAN-MNT-0001</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wizard-nav">
        <a href="{{ route('superadmin.societies.index') }}" class="btn btn-secondary">Cancel</a>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-secondary wizard-prev" style="display: none;">
                <i class="fas fa-arrow-left"></i> Previous
            </button>
            <button type="button" class="btn btn-primary wizard-next">
                Next <i class="fas fa-arrow-right"></i>
            </button>
            <button type="submit" class="btn btn-primary wizard-submit" style="display: none;">
                <i class="fas fa-save"></i> Submit Society
            </button>
        </div>
    </div>
</form>
@endsection
