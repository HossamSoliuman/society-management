@extends('superadmin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Super Admin Dashboard</h1>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span id="dashboardLoader" style="display: none; color: var(--text-muted); font-size: 12px;">
                <i class="fas fa-circle-notch fa-spin"></i> Updating…
            </span>
            <div class="header-search" style="max-width: 200px;">
                <i class="fas fa-calendar search-icon"></i>
                <select id="dashboardRange" data-period-select data-loader="dashboardLoader"
                        data-url="{{ route('superadmin.dashboard.data') }}"
                        class="form-control"
                        style="padding-left: 36px; -webkit-appearance: none; appearance: none;">
                    @foreach($rangeOptions as $key => $label)
                        <option value="{{ $key }}" @selected($key === $range)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Dashboard</span>
    </div>
</div>

<div class="stats-grid stats-grid-3" id="dash-stats">
    @include('superadmin.dashboard.partials.stats', ['d' => $d])
</div>

<div class="grid-3">
    <div class="card" id="dash-revenue" style="grid-column: span 2;">
        @include('superadmin.dashboard.partials.revenue', ['d' => $d])
    </div>

    <div>
        @php
            $totalSoc = array_sum($societyStatus) ?: 1;
            $totalSub = array_sum($subscriptionStatus) ?: 1;
        @endphp
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title">Society Status</div>
            </div>
            <div class="card-body" style="display: flex; align-items: center; gap: 16px;">
                <x-chart type="doughnut" label="Society status"
                    :labels="['Active', 'Expired', 'Inactive']"
                    :datasets="[['data' => [$societyStatus['active'], $societyStatus['expired'], $societyStatus['inactive']], 'colors' => ['#10B981', '#F59E0B', '#94a3b8']]]"
                    :center-value="(string) array_sum($societyStatus)" center-label="Total" />
                <div class="chart-legend" style="flex: 1;">
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #10B981;"></span>
                        <span class="legend-label">Active Societies</span>
                        <span class="legend-value">{{ $societyStatus['active'] }} ({{ round(($societyStatus['active']/$totalSoc)*100, 1) }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #F59E0B;"></span>
                        <span class="legend-label">Expired Societies</span>
                        <span class="legend-value">{{ $societyStatus['expired'] }} ({{ round(($societyStatus['expired']/$totalSoc)*100, 1) }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #94a3b8;"></span>
                        <span class="legend-label">Inactive Societies</span>
                        <span class="legend-value">{{ $societyStatus['inactive'] }} ({{ round(($societyStatus['inactive']/$totalSoc)*100, 1) }}%)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Subscription Status</div>
            </div>
            <div class="card-body" style="display: flex; align-items: center; gap: 16px;">
                <x-chart type="doughnut" label="Subscription status"
                    :labels="['Active', 'Expired', 'Expiring Soon']"
                    :datasets="[['data' => [$subscriptionStatus['active'], $subscriptionStatus['expired'], $subscriptionStatus['expiring_soon']], 'colors' => ['#10B981', '#EF4444', '#F59E0B']]]"
                    :center-value="(string) array_sum($subscriptionStatus)" center-label="Total" />
                <div class="chart-legend" style="flex: 1;">
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #10B981;"></span>
                        <span class="legend-label">Active</span>
                        <span class="legend-value">{{ $subscriptionStatus['active'] }} ({{ round(($subscriptionStatus['active']/$totalSub)*100, 1) }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #EF4444;"></span>
                        <span class="legend-label">Expired</span>
                        <span class="legend-value">{{ $subscriptionStatus['expired'] }} ({{ round(($subscriptionStatus['expired']/$totalSub)*100, 1) }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #F59E0B;"></span>
                        <span class="legend-label">Expiring Soon</span>
                        <span class="legend-value">{{ $subscriptionStatus['expiring_soon'] }} ({{ round(($subscriptionStatus['expiring_soon']/$totalSub)*100, 1) }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-3">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Societies</div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Society Name</th>
                        <th>Units</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSocieties as $society)
                    <tr>
                        <td style="padding-left: 20px;">
                            <div class="society-row">
                                <div class="society-icon"><i class="fas fa-building"></i></div>
                                <div class="society-details">
                                    <h4>{{ $society->name }}</h4>
                                    <p>{{ $society->city }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $society->total_units }}</td>
                        <td><span class="prefix-tag">{{ $society->subscriptionPlan->name ?? 'N/A' }}</span></td>
                        <td><span class="status-badge {{ $society->status }}">{{ ucfirst($society->status) }}</span></td>
                        <td>{{ $society->subscription_end_date ? $society->subscription_end_date->format('d M Y') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('superadmin.societies.index') }}" class="btn-link" style="display: flex; align-items: center; gap: 6px;">
                View All Societies <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
            </a>
        </div>
    </div>

    <div class="card" id="dash-plans">
        @include('superadmin.dashboard.partials.plans', ['d' => $d])
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Payments</div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Society</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                    <tr>
                        <td style="padding-left: 20px;">{{ $payment->society->name ?? 'N/A' }}</td>
                        <td>&#8377; {{ number_format($payment->amount) }}</td>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                        <td><span class="status-badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('superadmin.billing.payments') }}" class="btn-link" style="display: flex; align-items: center; gap: 6px;">
                View All Payments <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
            </a>
        </div>
    </div>
</div>

<div class="grid-3" style="margin-top: 8px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Quick Actions</div>
        </div>
        <div class="card-body">
            <div class="quick-actions" style="grid-template-columns: repeat(3, 1fr);">
                <a href="{{ route('superadmin.societies.create') }}" class="quick-action-item">
                    <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
                    <span>Add Society</span>
                </a>
                <a href="{{ route('superadmin.subscription.plans.create') }}" class="quick-action-item">
                    <i class="fas fa-tags" style="color: var(--success);"></i>
                    <span>Plan Management</span>
                </a>
                <a href="{{ route('superadmin.subscription.subscriptions.create') }}" class="quick-action-item">
                    <i class="fas fa-file-contract" style="color: var(--purple);"></i>
                    <span>Subscriptions</span>
                </a>
                <a href="{{ route('superadmin.billing.payments') }}" class="quick-action-item">
                    <i class="fas fa-money-bill-wave" style="color: var(--warning);"></i>
                    <span>Payments</span>
                </a>
                <a href="{{ route('superadmin.billing.invoices') }}" class="quick-action-item">
                    <i class="fas fa-file-invoice" style="color: var(--danger);"></i>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('superadmin.reports.index') }}" class="quick-action-item">
                    <i class="fas fa-chart-pie" style="color: var(--info);"></i>
                    <span>Reports</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Support Desk</div>
        </div>
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <div>
                <div style="font-size: 32px; font-weight: 700;">{{ $openTickets }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Open tickets</div>
            </div>
            <a href="{{ route('superadmin.tickets.index', ['status' => 'open']) }}" class="btn btn-secondary btn-sm">View <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Total Users</div>
        </div>
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 32px; font-weight: 700;">{{ $totalUsers }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Active Users</div>
            </div>
            <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--primary-light); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-users" style="color: var(--primary); font-size: 24px;"></i>
            </div>
        </div>
    </div>
</div>
@endsection
