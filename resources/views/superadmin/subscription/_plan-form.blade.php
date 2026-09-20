@php
    $plan = $plan ?? null;
    $enabledModules = old('modules', $plan ? $plan->enabledModuleKeys() : array_keys(\App\Models\SubscriptionPlan::MODULES));
    $selectedColor = old('color', $plan->color ?? '#E84B1E');
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ $errors->first() }}
    </div>
@endif

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <div class="section-title"><i class="fas fa-tag"></i> Plan Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Plan Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Enter plan name" value="{{ old('name', $plan->name ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Plan Type <span class="required">*</span></label>
                    <select name="plan_type" class="form-control" required>
                        <option value="">Select plan type</option>
                        @foreach(['basic' => 'Basic', 'standard' => 'Standard', 'premium' => 'Premium', 'enterprise' => 'Enterprise'] as $value => $label)
                            <option value="{{ $value }}" {{ old('plan_type', $plan->plan_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Select the category of this plan</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Plan Code <span class="required">*</span></label>
                    <input type="text" name="code" class="form-control" placeholder="Enter plan code (e.g. PREMIUM)" value="{{ old('code', $plan->code ?? '') }}" required>
                    <div class="form-text">Unique code for internal reference</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">&#8377;</span>
                        <input type="number" name="amount" class="form-control" placeholder="Enter amount" step="0.01" min="0" value="{{ old('amount', $plan->amount ?? '') }}" required>
                    </div>
                    <div class="form-text">Set the price for this plan</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Max Units <span class="required">*</span></label>
                    <input type="number" name="max_units" class="form-control" placeholder="e.g. 100" min="1" value="{{ old('max_units', $plan->max_units ?? 100) }}" required>
                    <div class="form-text">Maximum flats/units a society can manage on this plan</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Plan Duration <span class="required">*</span></label>
                    <select name="plan_duration" class="form-control" required>
                        <option value="">Select duration</option>
                        @foreach(['1_month' => '1 Month', '3_months' => '3 Months', '6_months' => '6 Months', '1_year' => '1 Year', '2_years' => '2 Years'] as $value => $label)
                            <option value="{{ $value }}" {{ old('plan_duration', $plan->plan_duration ?? '1_year') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Duration for which this plan is valid</div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Plan Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter plan description">{{ old('description', $plan->description ?? '') }}</textarea>
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
                        <option value="active" {{ old('status', $plan->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $plan->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Billing Cycle <span class="required">*</span></label>
                    <select name="billing_cycle" class="form-control" required>
                        <option value="">Select billing cycle</option>
                        @foreach(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'half_yearly' => 'Half Yearly', 'yearly' => 'Yearly'] as $value => $label)
                            <option value="{{ $value }}" {{ old('billing_cycle', $plan->billing_cycle ?? 'yearly') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">How often this plan will be billed</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Trial Period (Optional)</label>
                    <input type="number" name="trial_period_days" class="form-control" placeholder="0" value="{{ old('trial_period_days', $plan->trial_period_days ?? 0) }}" min="0">
                    <div class="form-text">Enter 0 for no trial period</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Plan Badge (Optional)</label>
                    <input type="text" name="badge" class="form-control" placeholder="e.g. Popular, Best Value" value="{{ old('badge', $plan->badge ?? '') }}">
                    <div class="form-text">Displayed as a badge on plan</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Plan Priority</label>
                    <input type="number" name="priority" class="form-control" placeholder="0" value="{{ old('priority', $plan->priority ?? 0) }}" min="0">
                    <div class="form-text">Higher number shows first in listings</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Plan Color (Optional)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="color" name="color" id="planColor" class="form-control" value="{{ $selectedColor }}" style="width: 50px; padding: 4px; height: 40px;">
                        <input type="text" id="planColorText" class="form-control" value="{{ $selectedColor }}" readonly>
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
                    <input type="checkbox" id="selectAllModules" {{ count($enabledModules) === count(\App\Models\SubscriptionPlan::MODULES) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Enable or disable access to modules in this plan. Permissions for each module will be applied automatically.</div>

        <div class="modules-grid">
            @foreach(\App\Models\SubscriptionPlan::MODULES as $key => $module)
            <div class="module-item">
                <div class="module-icon" style="background: var(--{{ $module['color'] }}-light); color: var(--{{ $module['color'] }});">
                    <i class="fas {{ $module['icon'] }}"></i>
                </div>
                <div class="module-info" style="flex: 1;">
                    <h4>{{ $module['name'] }}</h4>
                    <p>{{ $module['description'] }}</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="modules[]" value="{{ $key }}" class="module-toggle" {{ in_array($key, $enabledModules, true) ? 'checked' : '' }}>
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

@push('scripts')
<script>
    (function () {
        var color = document.getElementById('planColor');
        var colorText = document.getElementById('planColorText');
        var selectAll = document.getElementById('selectAllModules');
        var toggles = document.querySelectorAll('.module-toggle');

        color.addEventListener('input', function () { colorText.value = this.value; });

        selectAll.addEventListener('change', function () {
            var checked = this.checked;
            toggles.forEach(function (t) { t.checked = checked; });
        });

        toggles.forEach(function (t) {
            t.addEventListener('change', function () {
                selectAll.checked = Array.prototype.every.call(toggles, function (m) { return m.checked; });
            });
        });
    })();
</script>
@endpush
