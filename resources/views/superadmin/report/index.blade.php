@extends('superadmin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Reports</h1>
        </div>
        <div class="action-toolbar-right">
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Reports</span>
    </div>
</div>

<div class="grid-3">
    @php $reports = [
        ['icon' => 'fa-building', 'color' => 'blue', 'title' => 'Society Report', 'desc' => 'View detailed reports about societies.', 'route' => 'superadmin.reports.society'],
        ['icon' => 'fa-rupee-sign', 'color' => 'green', 'title' => 'Revenue Report', 'desc' => 'Track revenue and financial performance.', 'route' => 'superadmin.reports.revenue'],
        ['icon' => 'fa-credit-card', 'color' => 'purple', 'title' => 'Subscription Report', 'desc' => 'Monitor subscription status and renewals.', 'route' => 'superadmin.reports.subscription'],
        ['icon' => 'fa-money-bill-wave', 'color' => 'orange', 'title' => 'Payment Report', 'desc' => 'Analyze payment trends and methods.', 'route' => 'superadmin.reports.payment'],
    ]; @endphp
    @foreach($reports as $report)
    <div class="card">
        <div class="card-body" style="display: flex; align-items: flex-start; gap: 16px;">
            <div class="stat-icon {{ $report['color'] }}" style="width: 48px; height: 48px; font-size: 20px;">
                <i class="fas {{ $report['icon'] }}"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">{{ $report['title'] }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">{{ $report['desc'] }}</div>
                <a href="{{ route($report['route']) }}" class="btn btn-outline-primary btn-sm" style="font-size: 12px;"><i class="fas fa-eye"></i> View Report</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
