@extends('superadmin.layouts.app')

@section('title', $plan->name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">
                {{ $plan->name }}
                @if($plan->badge)<span class="badge badge-primary" style="font-size: 11px; vertical-align: middle; margin-left: 8px;">{{ $plan->badge }}</span>@endif
            </h1>
        </div>
        <div class="action-toolbar-right">
            <form action="{{ route('superadmin.subscription.plans.toggle-status', $plan) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i class="fas {{ $plan->status === 'active' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                    {{ $plan->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('superadmin.subscription.plans.edit', $plan) }}" class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.plans') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $plan->name }}</span>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
            <div class="stat-label">Plan Amount</div>
            <div class="stat-value">&#8377; {{ number_format($plan->amount, 2) }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Billed {{ str_replace('_', ' ', $plan->billing_cycle) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-cube"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Subscriptions</div>
            <div class="stat-value">{{ $plan->subscriptions_count }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">All time</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Subscriptions</div>
            <div class="stat-value">{{ $plan->active_subscriptions_count }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Active or expiring soon</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-th-large"></i></div>
        <div class="stat-info">
            <div class="stat-label">Modules Enabled</div>
            <div class="stat-value">{{ count($plan->enabledModuleKeys()) }} / {{ count(\App\Models\SubscriptionPlan::MODULES) }}</div>
            <div class="stat-trend" style="color: var(--text-muted);">Feature access</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-tag" style="color: var(--primary); margin-right: 8px;"></i>Plan Information</div>
        </div>
        <div class="card-body">
            <div class="review-row">
                <span class="review-label">Plan Name</span>
                <span class="review-value">{{ $plan->name }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Plan Code</span>
                <span class="prefix-tag">{{ $plan->code }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Plan Type</span>
                <span class="review-value">{{ ucfirst($plan->plan_type) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Status</span>
                <span class="status-badge {{ $plan->status }}">{{ ucfirst($plan->status) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Max Units</span>
                <span class="review-value">{{ number_format($plan->max_units) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Description</span>
                <span class="review-value">{{ $plan->description ?: 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-sliders-h" style="color: var(--warning); margin-right: 8px;"></i>Billing & Settings</div>
        </div>
        <div class="card-body">
            <div class="review-row">
                <span class="review-label">Amount</span>
                <span class="review-value" style="font-weight: 600;">&#8377; {{ number_format($plan->amount, 2) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Billing Cycle</span>
                <span class="review-value">{{ ucfirst(str_replace('_', ' ', $plan->billing_cycle)) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Plan Duration</span>
                <span class="review-value">{{ ucfirst(str_replace('_', ' ', $plan->plan_duration)) }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Trial Period</span>
                <span class="review-value">{{ $plan->trial_period_days ? $plan->trial_period_days . ' days' : 'None' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Priority</span>
                <span class="review-value">{{ $plan->priority }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Badge & Color</span>
                <span class="review-value" style="display: inline-flex; align-items: center; gap: 8px;">
                    <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: {{ $plan->color ?: '#2563EB' }}; border: 1px solid var(--border-color);"></span>
                    {{ $plan->badge ?: 'No badge' }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-th-large" style="color: var(--primary); margin-right: 8px;"></i>Module Access</div>
    </div>
    <div class="card-body">
        @php $enabled = $plan->enabledModuleKeys(); @endphp
        <div class="modules-grid">
            @foreach(\App\Models\SubscriptionPlan::MODULES as $key => $module)
            <div class="module-item" style="{{ in_array($key, $enabled, true) ? '' : 'opacity: .5;' }}">
                <div class="module-icon" style="background: var(--{{ $module['color'] }}-light); color: var(--{{ $module['color'] }});">
                    <i class="fas {{ $module['icon'] }}"></i>
                </div>
                <div class="module-info" style="flex: 1;">
                    <h4>{{ $module['name'] }}</h4>
                    <p>{{ $module['description'] }}</p>
                </div>
                <span class="status-badge {{ in_array($key, $enabled, true) ? 'active' : 'inactive' }}">{{ in_array($key, $enabled, true) ? 'Enabled' : 'Disabled' }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-building" style="color: var(--success); margin-right: 8px;"></i>Recent Subscriptions</div>
        <a href="{{ route('superadmin.subscription.subscriptions') }}" class="btn btn-secondary">View all</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subscription #</th>
                        <th>Society</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSubscriptions as $sub)
                    <tr>
                        <td style="font-weight: 600;">{{ $sub->subscription_number }}</td>
                        <td>{{ $sub->society->name ?? 'N/A' }}</td>
                        <td style="font-size: 12px;">{{ $sub->start_date->format('d M Y') }} – {{ $sub->end_date->format('d M Y') }}</td>
                        <td>&#8377; {{ number_format($sub->amount ?? 0, 2) }}</td>
                        <td><span class="status-badge {{ $sub->status }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">No societies are subscribed to this plan yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
