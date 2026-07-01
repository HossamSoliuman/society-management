@extends('society.layouts.app')

@section('title', 'Society Dashboard')

@php
    // Indian digit-grouping formatter (e.g. 245800 -> 2,45,800)
    $inr = function ($num) {
        $num = (string) (int) $num;
        $last3 = substr($num, -3);
        $rest = substr($num, 0, -3);
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            return $rest.','.$last3;
        }
        return $last3;
    };
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Society Dashboard</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Dashboard</span>
            </div>
        </div>
        <div class="header-search" style="max-width: 280px;">
            <i class="fas fa-calendar search-icon"></i>
            <input type="text" value="30 May – 30 Jun 2025" readonly style="padding-right: 36px;">
            <i class="fas fa-chevron-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 11px;"></i>
        </div>
    </div>
</div>

{{-- Row A — Stats --}}
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
    @include('society.partials.stat-card', ['icon' => 'fa-users', 'iconVariant' => 'peach', 'label' => 'Total Members', 'value' => number_format($stats['total_members']), 'trend' => $stats['new_members'].' this month', 'trendType' => 'up'])
    @include('society.partials.stat-card', ['icon' => 'fa-building', 'iconVariant' => 'peach', 'label' => 'Total Units', 'value' => number_format($stats['total_units']), 'trend' => $stats['occupancy_pct'].'% Occupied', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'peach', 'label' => 'Monthly Collections', 'value' => '&#8377; '.$inr($stats['monthly_collections']), 'trend' => $stats['collected_pct'].'% Collected', 'trendType' => 'success'])
    @include('society.partials.stat-card', ['icon' => 'fa-clipboard-list', 'iconVariant' => 'peach', 'label' => 'Pending Dues', 'value' => '&#8377; '.$inr($stats['pending_dues']), 'trend' => $stats['pending_members'].' Members', 'trendType' => 'warning'])
    @include('society.partials.stat-card', ['icon' => 'fa-triangle-exclamation', 'iconVariant' => 'peach', 'label' => 'Open Complaints', 'value' => number_format($stats['open_complaints']), 'trend' => $stats['high_priority'].' High Priority', 'trendType' => 'danger'])
</div>

{{-- Row B --}}
<div class="grid-3">
    {{-- Collection Overview --}}
    <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header"><div class="card-title">Collection Overview</div></div>
        <div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
            @include('society.partials.donut', [
                'segments' => [
                    ['value' => $collection['collected'], 'color' => '#10B981'],
                    ['value' => $collection['pending'], 'color' => '#F59E0B'],
                    ['value' => $collection['overdue'], 'color' => '#EF4444'],
                ],
                'centerValue' => $stats['collected_pct'].'%',
                'centerLabel' => 'Collected',
                'size' => 150,
                'stroke' => 13,
            ])
            <div class="chart-legend" style="flex: 1;">
                <div class="legend-item">
                    <span class="legend-dot" style="background: #10B981;"></span>
                    <span class="legend-label">Collected</span>
                    <span class="legend-value">&#8377; {{ $inr($collection['collected']) }} (88%)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #F59E0B;"></span>
                    <span class="legend-label">Pending</span>
                    <span class="legend-value">&#8377; {{ $inr($collection['pending']) }} (12%)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #EF4444;"></span>
                    <span class="legend-label">Overdue</span>
                    <span class="legend-value">&#8377; {{ $inr($collection['overdue']) }} (7%)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #cbd5e1;"></span>
                    <span class="legend-label">Total Demand</span>
                    <span class="legend-value">&#8377; {{ $inr($collection['total_demand']) }}</span>
                </div>
            </div>
        </div>
        <div class="card-footer" style="padding: 12px 20px;">
            <a href="{{ route('society.placeholder', ['page' => 'Collection Report']) }}" class="btn btn-outline-primary" style="width: 100%;">View Collection Report <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
        </div>
    </div>

    {{-- Monthly Revenue Trend --}}
    <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header">
            <div class="card-title">Monthly Revenue Trend</div>
            <select class="form-control" style="width: auto; min-width: 110px;">
                <option>This Year</option>
                <option>Last Year</option>
            </select>
        </div>
        <div class="card-body" style="flex: 1;">
            @php
                $max = 300000;
                $left = 38; $right = 315; $top = 12; $bottom = 150;
                $span = $right - $left;
                $count = count($revenue['points']);
                $coords = [];
                foreach ($revenue['points'] as $i => $val) {
                    $x = $left + ($count > 1 ? $i * ($span / ($count - 1)) : 0);
                    $y = $bottom - ($val / $max) * ($bottom - $top);
                    $coords[] = [round($x, 1), round($y, 1)];
                }
                $polyline = implode(' ', array_map(fn ($c) => $c[0].','.$c[1], $coords));
                $areaPath = 'M'.$coords[0][0].','.$bottom.' L'.implode(' L', array_map(fn ($c) => $c[0].','.$c[1], $coords)).' L'.end($coords)[0].','.$bottom.' Z';
            @endphp
            <svg viewBox="0 0 330 170" style="width: 100%; height: 200px;">
                {{-- y grid labels --}}
                @foreach(['₹3L' => 300000, '₹2L' => 200000, '₹1L' => 100000, '₹0' => 0] as $label => $val)
                    @php $gy = $bottom - ($val / $max) * ($bottom - $top); @endphp
                    <line x1="{{ $left }}" y1="{{ $gy }}" x2="{{ $right }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1"/>
                    <text x="{{ $left - 6 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="9" fill="#94a3b8">{{ $label }}</text>
                @endforeach
                {{-- area + line --}}
                <path d="{{ $areaPath }}" fill="rgba(232,75,30,0.10)"/>
                <polyline points="{{ $polyline }}" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                @foreach($coords as $c)
                    <circle cx="{{ $c[0] }}" cy="{{ $c[1] }}" r="3.5" fill="#fff" stroke="var(--primary)" stroke-width="2"/>
                @endforeach
                {{-- x labels --}}
                @foreach($revenue['months'] as $i => $m)
                    <text x="{{ $coords[$i][0] }}" y="165" text-anchor="middle" font-size="9" fill="#94a3b8">{{ $m }}</text>
                @endforeach
            </svg>
            <div style="display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Total Revenue (YTD)</div>
                    <div style="font-size: 15px; font-weight: 700; color: var(--success);">&#8377; {{ $inr($revenue['ytd']) }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: var(--text-muted);">vs Last Year</div>
                    <div style="font-size: 15px; font-weight: 700; color: var(--success);"><i class="fas fa-arrow-up" style="font-size: 11px;"></i> {{ $revenue['vs_last_year'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header"><div class="card-title">Recent Activity</div></div>
        <div class="card-body" style="flex: 1;">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($activities as $activity)
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div class="activity-icon {{ $activity['variant'] }}"><i class="fas {{ $activity['icon'] }}"></i></div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $activity['title'] }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $activity['subtitle'] }}</div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <div style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">{{ $activity['time'] }}</div>
                            @if($activity['amount'])
                                <div style="font-size: 13px; font-weight: 700; color: var(--success);">&#8377; {{ $activity['amount'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer" style="padding: 12px 20px;">
            <a href="{{ route('society.placeholder', ['page' => 'Activity Logs']) }}" class="btn btn-outline-secondary" style="width: 100%;">View All Activity <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
        </div>
    </div>
</div>

{{-- Row C — 2fr / 1fr --}}
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;" class="dashboard-row-c">
    <div>
        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="{{ route('society.members.create') }}" class="quick-action-item">
                        <i class="fas fa-user-plus" style="color: var(--primary);"></i><span>Add Member</span>
                    </a>
                    <a href="{{ route('society.units.create') }}" class="quick-action-item">
                        <i class="fas fa-house-circle-plus" style="color: var(--success);"></i><span>Add Unit</span>
                    </a>
                    <a href="{{ route('society.placeholder', ['page' => 'Create Bill']) }}" class="quick-action-item">
                        <i class="fas fa-file-invoice" style="color: var(--primary);"></i><span>Create Invoice</span>
                    </a>
                    <a href="{{ route('society.placeholder', ['page' => 'Collections']) }}" class="quick-action-item">
                        <i class="fas fa-credit-card" style="color: var(--info);"></i><span>Record Payment</span>
                    </a>
                    <a href="{{ route('society.placeholder', ['page' => 'Complaint Management']) }}" class="quick-action-item">
                        <i class="fas fa-headset" style="color: var(--danger);"></i><span>Raise Complaint</span>
                    </a>
                    <a href="{{ route('society.placeholder', ['page' => 'Notifications']) }}" class="quick-action-item">
                        <i class="fas fa-paper-plane" style="color: var(--primary);"></i><span>Send Notice</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Complaint Summary + Occupancy Overview --}}
        <div class="grid-2">
            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-header"><div class="card-title">Complaint Summary</div></div>
                <div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
                    @include('society.partials.donut', [
                        'segments' => [
                            ['value' => $complaintSummary['open'], 'color' => '#EF4444'],
                            ['value' => $complaintSummary['in_progress'], 'color' => '#F59E0B'],
                            ['value' => $complaintSummary['resolved'], 'color' => '#10B981'],
                        ],
                        'centerValue' => (string) $complaintSummary['open'],
                        'centerLabel' => 'Total',
                        'size' => 120,
                        'stroke' => 14,
                    ])
                    <div class="chart-legend" style="flex: 1;">
                        <div class="legend-item"><span class="legend-dot" style="background: #EF4444;"></span><span class="legend-label">Open</span><span class="legend-value">18 (60%)</span></div>
                        <div class="legend-item"><span class="legend-dot" style="background: #F59E0B;"></span><span class="legend-label">In Progress</span><span class="legend-value">8 (26%)</span></div>
                        <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Resolved</span><span class="legend-value">4 (14%)</span></div>
                    </div>
                </div>
                <div class="card-footer" style="padding: 12px 20px;">
                    <a href="{{ route('society.placeholder', ['page' => 'Complaint Management']) }}" class="btn-link">View Complaints <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column;">
                <div class="card-header"><div class="card-title">Occupancy Overview</div></div>
                <div class="card-body" style="flex: 1; display: flex; align-items: center; gap: 16px;">
                    @include('society.partials.donut', [
                        'segments' => [
                            ['value' => $occupancy['occupied'], 'color' => '#10B981'],
                            ['value' => $occupancy['vacant'], 'color' => '#3B82F6'],
                        ],
                        'centerValue' => (string) $stats['total_units'],
                        'centerLabel' => 'Total Units',
                        'size' => 120,
                        'stroke' => 14,
                    ])
                    <div class="chart-legend" style="flex: 1;">
                        <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span><span class="legend-label">Occupied</span><span class="legend-value">110 (92%)</span></div>
                        <div class="legend-item"><span class="legend-dot" style="background: #3B82F6;"></span><span class="legend-label">Vacant</span><span class="legend-value">10 (8%)</span></div>
                    </div>
                </div>
                <div class="card-footer" style="padding: 12px 20px;">
                    <a href="{{ route('society.units.index') }}" class="btn-link">View Units <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- Notice Board --}}
    <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header">
            <div class="card-title">Notice Board</div>
            <a href="{{ route('society.placeholder', ['page' => 'Notifications']) }}" class="btn-link">View All</a>
        </div>
        <div class="card-body" style="flex: 1;">
            <div style="display: flex; flex-direction: column; gap: 18px;">
                @foreach($notices as $notice)
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div class="activity-icon {{ $notice['variant'] }}"><i class="fas {{ $notice['icon'] }}"></i></div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; gap: 8px;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $notice['title'] }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">{{ $notice['date'] }}</div>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $notice['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer" style="padding: 12px 20px;">
            <a href="{{ route('society.placeholder', ['page' => 'Notifications']) }}" class="btn btn-outline-primary" style="width: 100%;">View All Notices <i class="fas fa-arrow-right" style="font-size: 10px;"></i></a>
        </div>
    </div>
</div>

{{-- Row D — Society info strip --}}
<div class="card">
    <div class="info-strip">
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-building"></i></div>
            <div><div class="info-strip-label">Society Name</div><div class="info-strip-value">{{ $societyInfo['name'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-location-dot"></i></div>
            <div><div class="info-strip-label">Address</div><div class="info-strip-value">{{ $societyInfo['address'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-calendar"></i></div>
            <div><div class="info-strip-label">Established</div><div class="info-strip-value">{{ $societyInfo['established'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-city"></i></div>
            <div><div class="info-strip-label">Total Towers</div><div class="info-strip-value">{{ $societyInfo['towers'] }}</div></div>
        </div>
        <div class="info-strip-cell">
            <div class="info-strip-icon"><i class="fas fa-layer-group"></i></div>
            <div><div class="info-strip-label">Total Floors</div><div class="info-strip-value">{{ $societyInfo['floors'] }}</div></div>
        </div>
    </div>
</div>
@endsection
