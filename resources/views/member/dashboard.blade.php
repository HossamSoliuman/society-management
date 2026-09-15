@extends('member.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Welcome, {{ Str::before($member->name, ' ') }}</h1>
            <p class="page-subtitle" style="margin-top: 4px;">
                {{ $society->name }}{{ $member->flat_unit ? ' · Flat '.$member->flat_unit : '' }}{{ $member->tower_wing ? ' · '.$member->tower_wing : '' }}
            </p>
        </div>
        @if($stats['outstanding'] > 0)
            <a href="{{ route('member.bills.index', ['status' => 'pending']) }}" class="btn btn-primary"><i class="fas fa-credit-card"></i> Pay Dues</a>
        @endif
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => $stats['outstanding'] > 0 ? 'danger' : 'success', 'label' => 'Outstanding Dues', 'value' => '₹ '.format_inr($stats['outstanding']), 'trend' => $stats['overdue'] ? $stats['overdue'].' overdue bill'.($stats['overdue'] === 1 ? '' : 's') : 'All clear', 'trendType' => $stats['overdue'] ? 'danger' : 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-file-invoice', 'iconVariant' => 'warning', 'label' => 'Open Bills', 'value' => $openBills->count(), 'trend' => 'Awaiting payment', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-circle-check', 'iconVariant' => 'success', 'label' => 'Paid This Year', 'value' => '₹ '.format_inr($stats['paid_this_year']), 'trend' => now()->format('Y'), 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-headset', 'iconVariant' => 'purple', 'label' => 'Open Requests', 'value' => $stats['open_tickets'], 'trend' => 'Complaints & requests', 'trendType' => 'muted'])
</div>

<div class="content-grid" style="grid-template-columns: 2fr 1fr;">
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bills Due</h3>
                <a href="{{ route('member.bills.index') }}" class="btn btn-outline-secondary btn-sm">All bills</a>
            </div>
            <div class="card-body">
                @forelse($openBills as $bill)
                    <div class="detail-row" style="align-items: center;">
                        <div class="detail-row-icon"><i class="fas fa-file-invoice"></i></div>
                        <div class="detail-row-label">
                            <a href="{{ route('member.bills.show', $bill) }}">{{ $bill->bill_number }}</a>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $bill->bill_month }} · due {{ $bill->due_date?->format('d M Y') }}</div>
                        </div>
                        <div class="detail-row-value" style="text-align: right;">
                            <div style="font-weight: 700;">&#8377; {{ format_inr($bill->outstanding_amount) }}</div>
                            @include('society.partials.status-badge', ['class' => $bill->statusBadgeClass(), 'label' => $bill->statusLabel()])
                        </div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No bills are pending. You are all paid up.</div>
                @endforelse
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 class="card-title">Recent Payments</h3>
                <a href="{{ route('member.payments.index') }}" class="btn btn-outline-secondary btn-sm">All payments</a>
            </div>
            <div class="card-body">
                @forelse($recentPayments as $payment)
                    <div class="detail-row" style="align-items: center;">
                        <div class="detail-row-icon"><i class="fas {{ $payment->paymentModeIcon() }}"></i></div>
                        <div class="detail-row-label">
                            <a href="{{ route('member.payments.show', $payment) }}">{{ $payment->receipt_number }}</a>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $payment->receipt_date?->format('d M Y') }} · {{ $payment->paymentModeLabel() }}</div>
                        </div>
                        <div class="detail-row-value" style="text-align: right; font-weight: 700;">&#8377; {{ format_inr($payment->paid_amount) }}</div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No payments recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notices</h3>
                <a href="{{ route('member.notices.index') }}" class="btn btn-outline-secondary btn-sm">All</a>
            </div>
            <div class="card-body">
                @forelse($notices as $notice)
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <a href="{{ route('member.notices.show', $notice) }}" style="font-weight: 600; font-size: 13px;">{{ $notice->title }}</a>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $notice->publish_at?->format('d M Y') }} · <span class="badge {{ $notice->priorityBadgeClass() }}">{{ ucfirst($notice->priority) }}</span></div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No notices right now.</div>
                @endforelse
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 class="card-title">Open Requests</h3>
                <a href="{{ route('member.support.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New</a>
            </div>
            <div class="card-body">
                @forelse($openTickets as $ticket)
                    <div style="padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <a href="{{ route('member.support.show', $ticket) }}" style="font-weight: 600; font-size: 13px;">{{ $ticket->ticket_number }} · {{ Str::limit($ticket->subject, 40) }}</a>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $ticket->category }} · <span class="badge {{ $ticket->statusBadgeClass() }}">{{ $ticket->statusLabel() }}</span></div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">No open requests.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
