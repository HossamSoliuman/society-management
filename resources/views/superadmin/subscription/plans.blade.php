@extends('superadmin.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Subscription Plans</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.subscription.plans.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Plan</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.subscriptions') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Plans</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filter-bar">
            <div class="filter-item" style="flex: 1;"><div class="header-search" style="max-width: 100%;"><i class="fas fa-search search-icon"></i><input type="text" placeholder="Search plans..."></div></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Status</option></select></div>
            <div class="filter-item" style="flex: 0 0 auto;"><select class="form-control"><option>All Types</option></select></div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="form-check-input"></th>
                        <th>Plan Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Max Units</th>
                        <th>Billing Cycle</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $plan)
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <div style="font-weight: 600;">{{ $plan->name }}</div>
                            @if($plan->badge)<span class="badge badge-primary" style="font-size: 10px; margin-top: 4px;">{{ $plan->badge }}</span>@endif
                        </td>
                        <td><span class="prefix-tag">{{ $plan->code }}</span></td>
                        <td>{{ ucfirst($plan->plan_type) }}</td>
                        <td style="font-weight: 600;">&#8377; {{ number_format($plan->amount, 2) }}</td>
                        <td>{{ $plan->max_units }}</td>
                        <td>{{ ucfirst($plan->billing_cycle) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $plan->plan_duration)) }}</td>
                        <td><span class="status-badge {{ $plan->status }}">{{ ucfirst($plan->status) }}</span></td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('superadmin.subscription.plans.show', $plan) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('superadmin.subscription.plans.edit', $plan) }}" class="action-btn edit" title="Edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('superadmin.subscription.plans.toggle-status', $plan) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="action-btn" title="{{ $plan->status === 'active' ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $plan->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                                </form>
                                <form action="{{ route('superadmin.subscription.plans.destroy', $plan) }}" method="POST" style="display: inline;" data-confirm="Delete plan {{ $plan->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $plans])
    </div>
</div>
@endsection
