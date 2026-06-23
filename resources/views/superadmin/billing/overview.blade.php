@extends('superadmin.layouts.app')

@section('title', 'Revenue & Billing')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Revenue & Billing</h1>
            <p class="page-subtitle">Track collections, invoices and payments in one place.</p>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.billing.invoices') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create Invoice</a>
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Revenue & Billing</span>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Revenue (This Month)</div>
            <div class="stat-value">&#8377; {{ number_format($totalRevenue) }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 12.5% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Collected (This Month)</div>
            <div class="stat-value">&#8377; {{ number_format($totalCollected) }}</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> 10.8% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Outstanding</div>
            <div class="stat-value">&#8377; {{ number_format($totalOutstanding) }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 5.3% from last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-label">Overdue Amount</div>
            <div class="stat-value">&#8377; {{ number_format($totalOverdue) }}</div>
            <div class="stat-trend down"><i class="fas fa-arrow-down"></i> 8.2% from last month</div>
        </div>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.billing.overview') }}" class="sub-nav-tab active">Overview</a>
    <a href="{{ route('superadmin.billing.invoices') }}" class="sub-nav-tab">Invoices</a>
    <a href="{{ route('superadmin.billing.payments') }}" class="sub-nav-tab">Payments</a>
    <a href="{{ route('superadmin.billing.receipts') }}" class="sub-nav-tab">Receipts</a>
    <a href="{{ route('superadmin.billing.outstanding') }}" class="sub-nav-tab">Outstanding</a>
    <a href="{{ route('superadmin.billing.overdue') }}" class="sub-nav-tab">Overdue</a>
    <a href="{{ route('superadmin.billing.refunds') }}" class="sub-nav-tab">Refunds</a>
</div>

<div class="grid-3">
    <div class="card" style="grid-column: span 2;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">Revenue Overview</div>
            <select class="form-control" style="width: auto; min-width: 100px;"><option>Daily</option><option>Weekly</option><option>Monthly</option></select>
        </div>
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                    <span style="width: 10px; height: 10px; background: var(--primary); border-radius: 2px; display: inline-block;"></span>
                    <span>Collected</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                    <span style="width: 10px; height: 10px; background: var(--gray-300); border-radius: 2px; display: inline-block;"></span>
                    <span>Outstanding</span>
                </div>
            </div>
            <div style="height: 240px; display: flex; align-items: flex-end; gap: 6px; padding: 10px 0;">
                @for($i = 0; $i < 30; $i++)
                    @php $h1 = rand(30, 100); $h2 = rand(20, $h1 - 10); @endphp
                    <div style="flex: 1; display: flex; align-items: flex-end; justify-content: center; gap: 1px; height: 200px;">
                        <div style="width: 6px; background: var(--primary); border-radius: 2px 2px 0 0; height: {{ $h1 * 1.8 }}px; opacity: 0.8;"></div>
                        <div style="width: 6px; background: var(--gray-300); border-radius: 2px 2px 0 0; height: {{ $h2 * 1.8 }}px;"></div>
                    </div>
                @endfor
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted); margin-top: 8px;">
                <span>01 May</span>
                <span>06 May</span>
                <span>11 May</span>
                <span>16 May</span>
                <span>21 May</span>
                <span>26 May</span>
                <span>31 May</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Revenue By Category</div>
        </div>
        <div class="card-body" style="display: flex; align-items: center; gap: 16px;">
            <div class="pie-chart-container" style="width: 130px; height: 130px;">
                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                    @php $catOffset = 0; $catColors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899']; @endphp
                    @foreach($revenueByCategory as $cat)
                        @php $catPct = ($cat['percentage'] / 100) * 238.76; @endphp
                        <circle cx="50" cy="50" r="38" fill="none" stroke="{{ $catColors[$loop->index] }}" stroke-width="14"
                            stroke-dasharray="{{ $catPct }} {{ 238.76 - $catPct }}"
                            stroke-dashoffset="-{{ $catOffset }}"/>
                        @php $catOffset += $catPct; @endphp
                    @endforeach
                </svg>
                <div class="pie-chart-center">
                    <div class="label" style="font-size: 10px;">Total</div>
                    <div class="value" style="font-size: 16px;">&#8377; {{ number_format(collect($revenueByCategory)->sum('amount')) }}</div>
                </div>
            </div>
            <div class="chart-legend" style="flex: 1;">
                @foreach($revenueByCategory as $cat)
                <div class="legend-item">
                    <span class="legend-dot" style="background: {{ $catColors[$loop->index] }};"></span>
                    <span class="legend-label">{{ $cat['name'] }}</span>
                    <span class="legend-value">&#8377; {{ number_format($cat['amount']) }} ({{ $cat['percentage'] }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
        <div style="padding: 0 20px 16px;">
            <a href="#" class="btn-link" style="font-size: 12px;">View full report <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
        </div>
    </div>
</div>

<div class="grid-3">
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <div class="card-title">Recent Invoices</div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Invoice No.</th>
                        <th>Flat / Unit</th>
                        <th>Member Name</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentInvoices as $invoice)
                    <tr>
                        <td style="padding-left: 20px;">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->flat_number }}</td>
                        <td>{{ $invoice->member_name }}</td>
                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                        <td style="font-weight: 600;">&#8377; {{ number_format($invoice->total_amount, 2) }}</td>
                        <td><span class="status-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                <button class="action-btn"><i class="fas fa-download"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="{{ route('superadmin.billing.invoices') }}" class="btn-link" style="display: flex; align-items: center; gap: 6px;">
                View all invoices <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
            </a>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title">Collection Status</div>
            </div>
            <div class="card-body" style="display: flex; align-items: center; gap: 20px;">
                <div class="pie-chart-container" style="width: 120px; height: 120px;">
                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        @php
                            $colPct = ($collectionStatus['collected_percent'] / 100) * 238.76;
                            $outPct = ($collectionStatus['outstanding'] / ($collectionStatus['collected'] + $collectionStatus['outstanding'] + $collectionStatus['overdue'] ?: 1)) * 100 * 2.3876;
                            $ovdPct = ($collectionStatus['overdue'] / ($collectionStatus['collected'] + $collectionStatus['outstanding'] + $collectionStatus['overdue'] ?: 1)) * 100 * 2.3876;
                        @endphp
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#10B981" stroke-width="12" stroke-dasharray="{{ $colPct }} {{ 238.76 - $colPct }}" stroke-dashoffset="0"/>
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#F59E0B" stroke-width="12" stroke-dasharray="{{ $outPct }} {{ 238.76 - $outPct }}" stroke-dashoffset="-{{ $colPct }}"/>
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#EF4444" stroke-width="12" stroke-dasharray="{{ $ovdPct }} {{ 238.76 - $ovdPct }}" stroke-dashoffset="-{{ $colPct + $outPct }}"/>
                    </svg>
                    <div class="pie-chart-center">
                        <div class="value" style="font-size: 22px;">{{ $collectionStatus['collected_percent'] }}%</div>
                        <div class="label">Collected</div>
                    </div>
                </div>
                <div class="chart-legend" style="flex: 1;">
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #10B981;"></span>
                        <span class="legend-label">Collected</span>
                        <span class="legend-value">&#8377; {{ number_format($collectionStatus['collected']) }} ({{ $collectionStatus['collected_percent'] }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #F59E0B;"></span>
                        <span class="legend-label">Outstanding</span>
                        <span class="legend-value">&#8377; {{ number_format($collectionStatus['outstanding']) }} ({{ 100 - $collectionStatus['collected_percent'] - round(($collectionStatus['overdue']/max($totalRevenue,1))*100) }}%)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #EF4444;"></span>
                        <span class="legend-label">Overdue</span>
                        <span class="legend-value">&#8377; {{ number_format($collectionStatus['overdue']) }} ({{ round(($collectionStatus['overdue']/max($totalRevenue,1))*100) }}%)</span>
                    </div>
                </div>
            </div>
            <div style="padding: 0 20px 16px;">
                <a href="#" class="btn-link" style="font-size: 12px;">View details <i class="fas fa-arrow-right" style="font-size: 9px;"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            <div class="card-body" style="padding: 12px;">
                @php $actions = ['Create Invoice' => 'fa-file-invoice', 'Record Payment' => 'fa-money-bill-wave', 'Add Other Charge' => 'fa-plus-circle', 'Manage Charges' => 'fa-cog', 'Bulk Invoice' => 'fa-copy']; @endphp
                @foreach($actions as $label => $icon)
                <a href="#" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: var(--radius); text-decoration: none; color: var(--text-primary); font-size: 13px; font-weight: 500; transition: all 0.2s; margin-bottom: 4px;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 32px; height: 32px; border-radius: var(--radius); background: var(--primary-light); display: flex; align-items: center; justify-content: center;"><i class="fas {{ $icon }}" style="color: var(--primary); font-size: 13px;"></i></span>
                        {{ $label }}
                    </span>
                    <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 10px;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
