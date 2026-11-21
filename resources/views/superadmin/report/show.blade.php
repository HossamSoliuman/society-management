@extends('superadmin.layouts.app')

@section('title', $data['title'])

@section('content')
@php
    $routes = ['society' => 'superadmin.reports.society', 'revenue' => 'superadmin.reports.revenue', 'subscription' => 'superadmin.reports.subscription', 'payment' => 'superadmin.reports.payment'];
    $current = $routes[$report];
@endphp
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $data['title'] }}</h1>
        </div>
        <div class="action-toolbar-right" style="display: inline-flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route($current, request()->query() + ['export' => 'xlsx']) }}" class="btn btn-secondary" style="color: var(--success); border-color: var(--success);"><i class="fas fa-file-excel"></i> Excel</a>
            <a href="{{ route($current, request()->query() + ['export' => 'pdf']) }}" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> PDF</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.reports.index') }}">Reports</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $data['title'] }}</span>
    </div>
</div>

<div class="tabs">
    @foreach(\App\Services\PlatformReportService::REPORTS as $key => $label)
        <a href="{{ route($routes[$key]) }}" class="tab {{ $report === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route($current) }}" style="display: flex; gap: 12px; align-items: end; flex-wrap: wrap;">
            @if(in_array($report, ['revenue', 'payment'], true))
                <div class="form-group" style="margin-bottom: 0; flex: 1 1 200px;">
                    <label class="form-label">Society</label>
                    <select name="society" class="form-control">
                        <option value="">All societies</option>
                        @foreach($societies as $society)
                            <option value="{{ $society->id }}" @selected((string) request('society') === (string) $society->id)>{{ $society->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group" style="margin-bottom: 0; flex: 1 1 200px;">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ $report === 'society' ? 'Society, city or registration no.' : 'Society or subscription no.' }}">
                </div>
            @endif
            <div class="form-group" style="margin-bottom: 0;"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $data['period'][0] }}"></div>
            <div class="form-group" style="margin-bottom: 0;"><label class="form-label">To</label><input type="date" name="to" class="form-control @error('to') is-invalid @enderror" value="{{ $data['period'][1] }}"></div>

            @if($report === 'society')
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="">All</option>
                        @foreach($societyTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) request('type') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($report === 'subscription')
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        @foreach(['active' => 'Active', 'expiring_soon' => 'Expiring Soon', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Plan</label>
                    <select name="plan" class="form-control">
                        <option value="">All</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) request('plan') === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Method</label>
                    <select name="method" class="form-control">
                        <option value="">All</option>
                        @foreach($methods as $method)
                            <option value="{{ $method }}" @selected(request('method') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                @if($report === 'payment')
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            @foreach(['success' => 'Success', 'pending' => 'Pending', 'failed' => 'Failed'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endif

            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Apply</button>
            @if(request()->hasAny(['q', 'society', 'from', 'to', 'status', 'type', 'plan', 'method']))
                <a href="{{ route($current) }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
        @if($errors->any())
            <div class="invalid-feedback" style="display: block; margin-top: 8px;">{{ $errors->first() }}</div>
        @endif
    </div>
</div>

<div class="stats-grid stats-grid-{{ count($data['summary']) }}">
    @foreach($data['summary'] as $label => $value)
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value" style="font-size: 18px;">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr>@foreach($data['headings'] as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>@foreach($row as $cell)<td style="{{ is_float($cell) ? 'text-align: right; white-space: nowrap;' : '' }}">{{ is_float($cell) ? number_format($cell, 2) : ($cell ?? '—') }}</td>@endforeach</tr>
                    @empty
                        <tr><td colspan="{{ count($data['headings']) }}"><div class="empty-state"><div class="empty-state-title">No records for these filters</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
