@extends('society.layouts.app')

@section('title', 'My Subscription')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Subscription</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>My Subscription</span>
            </div>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-cube"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Plan</div>
                    <div class="stat-value" style="font-size: 18px;">{{ $current?->plan?->name ?? '—' }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $current ? ucfirst(str_replace('_', ' ', $current->billing_cycle)) : 'No active plan' }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon {{ $current?->status === 'active' ? 'green' : 'orange' }}"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Status</div>
                    <div class="stat-value" style="font-size: 18px;">{{ $current?->statusLabel() ?? ucwords(str_replace('_', ' ', $society->subscription_status)) }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>{{ $current ? $current->daysUntilExpiry().' days left' : '' }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-calendar"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Term</div>
                    <div class="stat-value" style="font-size: 14px;">{{ $current?->start_date?->format('d M Y') ?? '—' }} → {{ $current?->end_date?->format('d M Y') ?? '—' }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Access until {{ $current?->accessEndsAt()->format('d M Y') ?? '—' }} (incl. {{ $society->grace_period_days }} grace days)</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon {{ $outstanding > 0 ? 'red' : 'green' }}"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Outstanding</div>
                    <div class="stat-value" style="font-size: 18px;">{{ format_inr($outstanding) }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Platform invoices</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Invoices</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Category</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Outstanding</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td style="font-weight: 600;">{{ $inv->invoice_number }}</td>
                                    <td>{{ $inv->category ?? $inv->invoice_type }}</td>
                                    <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                                    <td>{{ $inv->due_date->format('d M Y') }}</td>
                                    <td>{{ format_inr($inv->total_amount) }}</td>
                                    <td>{{ format_inr($inv->paid_amount) }}</td>
                                    <td>{{ format_inr($inv->outstanding_amount) }}</td>
                                    <td><span class="badge {{ $inv->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $inv->status)) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-file-invoice"></i></div>
                                            <div class="empty-state-title">No invoices yet</div>
                                            <div class="empty-state-text">Platform invoices for your subscription will appear here.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('society.partials.pagination', ['items' => $invoices, 'unit' => 'invoices'])
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Subscription History</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Plan</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $sub)
                                <tr>
                                    <td style="font-weight: 600;">{{ $sub->subscription_number }} @if($sub->renewedFrom)<span style="font-size: 11px; color: var(--text-muted);">(renewal)</span>@endif</td>
                                    <td>{{ $sub->plan?->name }}</td>
                                    <td>{{ $sub->start_date->format('d M Y') }}</td>
                                    <td>{{ $sub->end_date->format('d M Y') }}</td>
                                    <td>{{ format_inr($sub->amount ?: ($sub->plan?->amount ?? 0)) }}</td>
                                    <td><span class="badge {{ $sub->statusBadgeClass() }}">{{ $sub->statusLabel() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Recent Payments</div>
                @forelse($payments as $payment)
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: var(--success-light); color: var(--success);"><i class="fas fa-indian-rupee-sign"></i></span>
                        <span class="rli-main">
                            <span class="rli-title">{{ format_inr($payment->amount) }}</span>
                            <span class="rli-sub">{{ $payment->receipt_number }} · {{ $payment->payment_method }}</span>
                        </span>
                        <span class="rli-meta">{{ $payment->payment_date->format('d M Y') }}</span>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No payments recorded yet.</div>
                @endforelse
            </div>
        </div>

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Renewals and upgrades are handled by the platform team. Contact support before your access end date to avoid interruption.</span>
        </div>
    </div>
</div>
@endsection
