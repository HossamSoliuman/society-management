@extends('society.layouts.app')

@section('title', 'Tenant Agreements')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Tenant Agreements</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenants.index') }}">Tenants</a>
                <span class="breadcrumb-separator">/</span>
                <span>Agreements</span>
            </div>
        </div>
        <a href="{{ route('society.tenants.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Tenant</a>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-file-signature', 'iconVariant' => 'success', 'label' => 'Running Agreements', 'value' => number_format($stats['active'])])
    @include('society.partials.stat-card', ['icon' => 'fa-hourglass-half', 'iconVariant' => 'warning', 'label' => 'Expiring in 30 Days', 'value' => number_format($stats['expiring'])])
    @include('society.partials.stat-card', ['icon' => 'fa-file-circle-xmark', 'iconVariant' => 'danger', 'label' => 'Expired, Not Renewed', 'value' => number_format($stats['expired'])])
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'info', 'label' => 'Monthly Rent Roll', 'value' => e(format_inr($stats['rent'], 0))])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.tenants.agreements') }}" style="display: grid; grid-template-columns: 1fr auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tenant, unit or agreement number...">
            </div>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Search</button>
            <a href="{{ route('society.tenants.agreements') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="tabs">
            @foreach($filters as $key => $label)
                <a href="{{ route('society.tenants.agreements', array_merge(request()->except(['filter', 'page']), ['filter' => $key])) }}" class="tab {{ $filter === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Unit</th>
                        <th>Period</th>
                        <th>Rent</th>
                        <th>Deposit</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td>
                                @if($agreement->tenant)
                                    <a href="{{ route('society.tenants.show', $agreement->tenant) }}" style="font-weight: 600;">{{ $agreement->tenant->name }}</a>
                                @else
                                    —
                                @endif
                                @if($agreement->agreement_number)<div style="font-size: 11px; color: var(--text-muted);">{{ $agreement->agreement_number }}</div>@endif
                            </td>
                            <td>{{ $agreement->unit?->unit_number ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $agreement->start_date->format('d M Y') }} – {{ $agreement->end_date->format('d M Y') }}</td>
                            <td style="white-space: nowrap;">{{ format_inr($agreement->monthly_rent, 0) }}</td>
                            <td style="white-space: nowrap;">{{ format_inr($agreement->security_deposit, 0) }}</td>
                            <td><span class="badge {{ $agreement->stateBadgeClass() }}">{{ $agreement->stateLabel() }}</span></td>
                            <td>
                                @if($agreement->document_path)
                                    <a href="{{ route('society.tenants.agreements.download', $agreement) }}" class="action-btn view" title="Download copy"><i class="fas fa-download"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-file-signature"></i></div>
                                    <div class="empty-state-title">No agreements found</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $agreements, 'unit' => 'agreements'])
    </div>
</div>
@endsection
