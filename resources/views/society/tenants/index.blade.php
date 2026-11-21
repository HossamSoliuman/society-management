@extends('society.layouts.app')

@section('title', 'Tenants')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Tenants</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Tenants</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 10px;">
            <a href="{{ route('society.tenants.agreements') }}" class="btn btn-secondary"><i class="fas fa-file-signature"></i> Agreements</a>
            <a href="{{ route('society.tenants.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Tenant</a>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-user-group', 'iconVariant' => 'peach', 'label' => 'Active Tenants', 'value' => number_format($stats['active'])])
    @include('society.partials.stat-card', ['icon' => 'fa-hourglass-half', 'iconVariant' => 'warning', 'label' => 'Agreements Expiring (30 days)', 'value' => number_format($stats['expiring'])])
    @include('society.partials.stat-card', ['icon' => 'fa-file-circle-xmark', 'iconVariant' => 'danger', 'label' => 'Expired Agreements', 'value' => number_format($stats['expired'])])
    @include('society.partials.stat-card', ['icon' => 'fa-shield-halved', 'iconVariant' => 'purple', 'label' => 'Police Verification Pending', 'value' => number_format($stats['verification'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.tenants.index') }}" style="display: grid; grid-template-columns: 1fr 170px 200px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, mobile or unit...">
            </div>
            <select name="status" class="form-control">
                <option value="active" @selected($status === 'active')>Current tenants</option>
                <option value="moved_out" @selected($status === 'moved_out')>Moved out</option>
            </select>
            <select name="verification" class="form-control">
                <option value="">Any verification</option>
                @foreach($verifications as $value => $label)
                    <option value="{{ $value }}" @selected(request('verification') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.tenants.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Unit</th>
                        <th>Owner</th>
                        <th>Move In</th>
                        <th>Agreement</th>
                        <th>Rent</th>
                        <th>Verification</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        @php($agreement = $tenant->currentAgreement)
                        <tr>
                            <td>
                                <a href="{{ route('society.tenants.show', $tenant) }}" style="font-weight: 600;">{{ $tenant->name }}</a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $tenant->mobile }} · {{ $tenant->occupants_count }} {{ Str::plural('occupant', $tenant->occupants_count) }}</div>
                            </td>
                            <td>{{ $tenant->unit?->unit_number ?? '—' }}</td>
                            <td>{{ $tenant->owner?->name ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $tenant->move_in_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                @if($agreement)
                                    <span class="badge {{ $agreement->stateBadgeClass() }}">{{ $agreement->stateLabel() }}</span>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">till {{ $agreement->end_date->format('d M Y') }}</div>
                                @else
                                    <span class="badge badge-secondary">None</span>
                                @endif
                            </td>
                            <td style="white-space: nowrap;">{{ $agreement ? format_inr($agreement->monthly_rent, 0) : '—' }}</td>
                            <td><span class="badge {{ $tenant->verificationBadgeClass() }}">{{ $tenant->verificationLabel() }}</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.tenants.show', $tenant) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.tenants.edit', $tenant) }}" class="action-btn edit" title="Edit"><i class="fas fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-group"></i></div>
                                    <div class="empty-state-title">No tenants found</div>
                                    <div class="empty-state-text">Add a tenant when an owner rents out a unit.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $tenants, 'unit' => 'tenants'])
    </div>
</div>
@endsection
