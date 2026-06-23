@extends('superadmin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Reports</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
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
        ['icon' => 'fa-users', 'color' => 'teal', 'title' => 'Member Report', 'desc' => 'Get insights on member activities.', 'route' => null],
        ['icon' => 'fa-file-invoice', 'color' => 'red', 'title' => 'Invoice Report', 'desc' => 'Review invoice statistics and trends.', 'route' => null],
        ['icon' => 'fa-clipboard-list', 'color' => 'pink', 'title' => 'Complaint Report', 'desc' => 'Track complaint resolution metrics.', 'route' => null],
        ['icon' => 'fa-chart-line', 'color' => 'info', 'title' => 'Growth Report', 'desc' => 'Analyze platform growth metrics.', 'route' => null],
        ['icon' => 'fa-cog', 'color' => 'gray', 'title' => 'Custom Report', 'desc' => 'Generate custom reports.', 'route' => null],
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
                @if($report['route'])
                    <a href="{{ route($report['route']) }}" class="btn btn-outline-primary btn-sm" style="font-size: 12px;"><i class="fas fa-eye"></i> View Report</a>
                @else
                    <span class="badge" style="background: var(--gray-100); color: var(--text-muted); font-size: 11px; padding: 4px 8px;">Coming Soon</span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
