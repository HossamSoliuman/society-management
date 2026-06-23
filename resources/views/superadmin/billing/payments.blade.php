@extends('superadmin.layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payments</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Record Payment</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.billing.overview') }}">Revenue & Billing</a>
        <span class="breadcrumb-separator">/</span>
        <span>Payments</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Collections (This Month)</div>
            <div class="stat-value">&#8377; {{ number_format($totalCollections) }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 10.8% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Payments Received (This Month)</div>
            <div class="stat-value">{{ $totalPaymentsReceived }}</div>
            <a href="#" style="font-size: 11px; color: var(--primary);">View details <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value">{{ $pendingPayments }}</div>
            <div style="font-size: 12px; color: var(--warning); font-weight: 600;">&#8377; {{ number_format($pendingAmount) }}</div>
            <a href="#" style="font-size: 11px; color: var(--primary);">View details <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Failed Payments</div>
            <div class="stat-value">{{ $failedPayments }}</div>
            <div style="font-size: 12px; color: var(--danger); font-weight: 600;">&#8377; {{ number_format($failedAmount) }}</div>
            <a href="#" style="font-size: 11px; color: var(--primary);">View details <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
        </div>
    </div>
</div>

<div class="sub-nav-tabs">
    @foreach(['overview' => 'Overview', 'invoices' => 'Invoices', 'payments' => 'Payments', 'receipts' => 'Receipts', 'outstanding' => 'Outstanding', 'overdue' => 'Overdue', 'refunds' => 'Refunds'] as $route => $label)
    <a href="{{ route('superadmin.billing.' . $route) }}" class="sub-nav-tab {{ $route == 'payments' ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-body">
            <div class="filter-bar">
                <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search by member, invoice no., receipt no..."></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Methods</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Types</option></select></div>
                <div class="filter-item" style="flex: 0 0 auto;"><div class="header-search" style="max-width: 200px;"><i class="fas fa-calendar search-icon"></i><input type="text" value="01 May 2025 - 31 May 2025" readonly></div></div>
                <div class="filter-item" style="flex: 0 0 auto;"><button class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filters</button></div>
            </div>

            <div style="margin-bottom: 16px;"><div class="section-title" style="font-size: 14px; margin: 0;"><i class="fas fa-list"></i> Payments List ({{ $totalPaymentsReceived }})</div></div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Receipt No.</th>
                            <th>Member / Flat</th>
                            <th>Invoice No.</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $payment->receipt_number }}</td>
                            <td><div class="user-name">{{ $payment->member_name }}</div><div class="user-email">{{ $payment->flat_number }}</div></td>
                            <td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                            <td style="font-weight: 600;">&#8377; {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @php $methodIcons = ['UPI' => 'fa-mobile-alt', 'Bank Transfer' => 'fa-university', 'Credit Card' => 'fa-credit-card', 'Net Banking' => 'fa-globe', 'Cash' => 'fa-money-bill-wave']; @endphp
                                <span class="payment-method"><i class="fas {{ $methodIcons[$payment->payment_method] ?? 'fa-money-bill' }}"></i> {{ $payment->payment_method }}</span>
                            </td>
                            <td><span class="status-badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td><div style="display: flex; gap: 4px;"><button class="action-btn view"><i class="fas fa-eye"></i></button><button class="action-btn"><i class="fas fa-ellipsis-h"></i></button></div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('superadmin.components.pagination', ['items' => $payments])
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"><div class="card-title">Payment Summary</div></div>
            <div class="card-body">
                @foreach(['Total Amount' => $totalCollections, 'Successful Payments' => $totalCollections - $pendingAmount, 'Pending Payments' => $pendingAmount, 'Failed Payments' => $failedAmount, 'Refunded Amount' => 6557] as $label => $val)
                <div class="summary-item">
                    <span class="summary-label">{{ $label }}</span>
                    <span class="summary-value" style="color: {{ $loop->index == 0 ? 'var(--text-primary)' : ($loop->index == 1 ? 'var(--success)' : ($loop->index == 2 ? 'var(--warning)' : ($loop->index == 3 ? 'var(--danger)' : 'var(--purple)'))) }}; font-weight: 700;">&#8377; {{ number_format($val, 2) }}</span>
                </div>
                @endforeach
                <div style="margin-top: 12px;"><a href="#" class="btn btn-outline-primary btn-sm" style="width: 100%;"><i class="fas fa-chart-bar"></i> View Payment Report</a></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Payment Methods Breakdown</div></div>
            <div class="card-body" style="display: flex; align-items: center; gap: 16px;">
                <div class="pie-chart-container" style="width: 100px; height: 100px;">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                        @php $pmOffset = 0; $pmColors = ['#2563EB', '#10B981', '#F59E0B', '#8B5CF6', '#64748b']; $pmPcts = [51, 30, 10, 6, 3]; @endphp
                        @foreach($pmPcts as $pi => $pct)
                            @php $dash = ($pct / 100) * 238.76; @endphp
                            <circle cx="50" cy="50" r="38" fill="none" stroke="{{ $pmColors[$pi] }}" stroke-width="10" stroke-dasharray="{{ $dash }} {{ 238.76 - $dash }}" stroke-dashoffset="-{{ $pmOffset }}"/>
                            @php $pmOffset += $dash; @endphp
                        @endforeach
                    </svg>
                    <div class="pie-chart-center">
                        <div class="value" style="font-size: 11px;">Total</div>
                        <div class="label" style="font-size: 14px; font-weight: 700;">&#8377; {{ number_format($totalCollections) }}</div>
                    </div>
                </div>
                <div class="chart-legend" style="flex: 1;">
                    @foreach(['UPI' => ['color' => 'primary', 'amt' => 145780, 'pct' => 51], 'Bank Transfer' => ['color' => 'success', 'amt' => 85600, 'pct' => 30], 'Net Banking' => ['color' => 'warning', 'amt' => 28450, 'pct' => 10], 'Credit Card' => ['color' => 'purple', 'amt' => 15600, 'pct' => 6], 'Others' => ['color' => 'secondary', 'amt' => 900, 'pct' => 3]] as $method => $d)
                    <div class="legend-item">
                        <span class="legend-dot" style="background: var(--{{ $d['color'] }});"></span>
                        <span class="legend-label">{{ $method }}</span>
                        <span class="legend-value">&#8377; {{ number_format($d['amt']) }} ({{ $d['pct'] }}%)</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div style="padding: 0 20px 16px;"><a href="#" class="btn-link" style="font-size: 12px;">View full breakdown <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a></div>
        </div>
    </div>
</div>
@endsection
