@extends('superadmin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Super Admin Dashboard</h1>
        </div>
        <div class="header-search" style="max-width: 280px;">
            <i class="fas fa-calendar search-icon"></i>
            <input type="text" value="22 Jun 2026 - 22 Jul 2026" readonly>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Dashboard</span>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Societies</div>
            <div class="stat-value">{{ number_format($totalSocieties) }}</div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>12 this month</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Societies</div>
            <div class="stat-value">{{ number_format($activeSocieties) }}</div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>8 this month</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-hourglass-end"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Expired Societies</div>
            <div class="stat-value">{{ number_format($expiredSocieties) }}</div>
            <div class="stat-trend down">
                <i class="fas fa-arrow-down"></i>
                <span>2 this month</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Units</div>
            <div class="stat-value">{{ number_format($totalUnits) }}</div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>356 this month</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-indian-rupee-sign"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Monthly Revenue</div>
            <div class="stat-value">&#8377; {{ number_format($currentMonthRevenue / 100000, 2) }}L</div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>18.6% this month</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon teal">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">&#8377; {{ number_format($totalRevenue / 100000, 2) }}L</div>
            <div class="stat-trend" style="color: var(--text-muted);">
                <span>All time revenue</span>
            </div>
        </div>
    </div>
</div>

<div class="grid-3">
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <div>
                <div class="card-title">Revenue Overview</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Monthly revenue for last 12 months</div>
            </div>
            <select class="form-control" style="width: auto; min-width: 140px;">
                <option>Last 12 Months</option>
                <option>Last 6 Months</option>
                <option>This Year</option>
            </select>
        </div>
        <div class="card-body">
            <div style="height: 280px; display: flex; align-items: flex-end; gap: 8px; padding: 20px 0;">
                @foreach($monthlyRevenue as $rev)
                    @php
                        $maxRev = collect($monthlyRevenue)->max('amount') ?: 1;
                        $height = ($rev['amount'] / $maxRev) * 220;
                    @endphp
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                        <div style="width: 100%; display: flex; align-items: flex-end; justify-content: center; gap: 2px; height: 220px;">
                            <div style="width: 8px; background: var(--primary); border-radius: 3px 3px 0 0; height: {{ $height }}px; opacity: 0.8;"></div>
                            <div style="width: 8px; background: var(--gray-300); border-radius: 3px 3px 0 0; height: {{ $height * 0.6 }}px;"></div>
                        </div>
                        <span style="font-size: 10px; color: var(--text-muted);">{{ $rev['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title">Society Status</div>
            </div>
            <div class="card-body" style="display: flex; align-items: center; gap: 16px;">
                <div class="pie-chart-container">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        @php
                            $totalSoc = array_sum($societyStatus);
                            $totalSoc = $totalSoc ?: 1;
                            $offset = 0;
                            $colors = ['#10B981', '#F59E0B', '#94a3b8'];
                            $keys = array_keys($societyStatus);
                        @endphp
                        @foreach($societyStatus as $idx => $value)
                            @php
                                $pct = ($value / $totalSoc) * 251.2;
                                $color = $colors[$loop->index] ?? '#e2e8f0';
                            @endphp
                            <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $color }}" stroke-width="12"
                                stroke-dasharray="{{ $pct }} {{ 251.2 - $pct }}"
                                stroke-dashoffset="-{{ $offset }}" stroke-linecap="butt"/>
                            @php $offset += $pct; @endphp
                        @endforeach
                    </svg>
                    <div class="pie-chart-center">
                        <div class="value">{{ $totalSocieties }}</div>
                        <div class="label">Total</div>
                    </div>
                </div>
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
                <div class="pie-chart-container">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        @php
                            $totalSub = array_sum($subscriptionStatus);
                            $totalSub = $totalSub ?: 1;
                            $offset = 0;
                            $subColors = ['#10B981', '#EF4444', '#F59E0B'];
                        @endphp
                        @foreach($subscriptionStatus as $idx => $value)
                            @php $pct = ($value / $totalSub) * 251.2; @endphp
                            <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $subColors[$loop->index] ?? '#e2e8f0' }}" stroke-width="12"
                                stroke-dasharray="{{ $pct }} {{ 251.2 - $pct }}"
                                stroke-dashoffset="-{{ $offset }}"/>
                            @php $offset += $pct; @endphp
                        @endforeach
                    </svg>
                    <div class="pie-chart-center">
                        <div class="value">{{ $totalSocieties }}</div>
                        <div class="label">Total</div>
                    </div>
                </div>
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
        <div class="card-footer">
            <a href="{{ route('superadmin.societies.index') }}" class="btn-link" style="display: flex; align-items: center; gap: 6px;">
                View All Societies <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Revenue by Plan</div>
        </div>
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="pie-chart-container" style="width: 140px; height: 140px;">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                        @php $rpOffset = 0; @endphp
                        @foreach($revenueByPlan as $plan)
                            @php $rpPct = ($plan['percentage'] / 100) * 251.2; @endphp
                            <circle cx="50" cy="50" r="40" fill="none" stroke="{{ ['#2563EB', '#10B981', '#F59E0B'][$loop->index] }}" stroke-width="14"
                                stroke-dasharray="{{ $rpPct }} {{ 251.2 - $rpPct }}"
                                stroke-dashoffset="-{{ $rpOffset }}"/>
                            @php $rpOffset += $rpPct; @endphp
                        @endforeach
                    </svg>
                </div>
                <div class="chart-legend" style="flex: 1;">
                    @foreach($revenueByPlan as $plan)
                    <div class="legend-item">
                        <span class="legend-dot" style="background: {{ ['#2563EB', '#10B981', '#F59E0B'][$loop->index] }};"></span>
                        <span class="legend-label">{{ $plan['name'] }}</span>
                        <span class="legend-value">&#8377; {{ number_format($plan['amount']) }} ({{ $plan['percentage'] }}%)</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-secondary);">Payment Received (This Month)</div>
                        <div style="font-size: 20px; font-weight: 700; margin-top: 4px;">&#8377; {{ number_format($currentMonthRevenue) }}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; color: var(--success); font-size: 12px; font-weight: 600;">
                        <i class="fas fa-arrow-up"></i> 18.6%
                    </div>
                </div>
                <div class="mini-chart" style="margin-top: 12px;">
                    @for($i = 0; $i < 20; $i++)
                        <div class="mini-chart-bar" style="height: {{ rand(20, 100) }}%;"></div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Payments</div>
        </div>
        <div class="card-body" style="padding: 0;">
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
            <div class="card-title">System Health</div>
        </div>
        <div class="card-body" style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--success-light); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-shield-alt" style="color: var(--success); font-size: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 600;">All systems are running smoothly</div>
                <div style="font-size: 12px; color: var(--success); margin-top: 4px;">
                    <i class="fas fa-check-circle"></i> All services operational
                </div>
            </div>
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
