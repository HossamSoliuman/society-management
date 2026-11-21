@extends('society.layouts.app')

@section('title', 'Staff')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Staff</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Staff</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 10px;">
            <a href="{{ route('society.staff.attendance.mark') }}" class="btn btn-secondary"><i class="fas fa-calendar-check"></i> Mark Today</a>
            <a href="{{ route('society.staff.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Staff</a>
        </div>
    </div>
</div>

<div class="stats-grid">
    @include('society.partials.stat-card', ['icon' => 'fa-users', 'iconVariant' => 'peach', 'label' => 'Active Staff', 'value' => number_format($stats['active'])])
    @include('society.partials.stat-card', ['icon' => 'fa-user-check', 'iconVariant' => 'success', 'label' => 'Present Today', 'value' => number_format($stats['present'])])
    @include('society.partials.stat-card', ['icon' => 'fa-user-xmark', 'iconVariant' => 'danger', 'label' => 'Absent Today', 'value' => number_format($stats['absent']), 'trend' => $stats['leave'].' on leave', 'trendType' => 'muted'])
    @include('society.partials.stat-card', ['icon' => 'fa-hourglass-half', 'iconVariant' => 'warning', 'label' => 'Not Marked Today', 'value' => number_format($stats['unmarked'])])
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'info', 'label' => 'Monthly Payroll', 'value' => e(format_inr($stats['payroll'], 0))])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.staff.index') }}" style="display: grid; grid-template-columns: 1fr 190px 160px 150px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, mobile or code...">
            </div>
            <select name="role" class="form-control">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <select name="shift" class="form-control">
                <option value="">All Shifts</option>
                @foreach($shifts as $value => $label)
                    <option value="{{ $value }}" @selected(request('shift') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control">
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.staff.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Shift</th>
                        <th>Mobile</th>
                        <th>Employer</th>
                        <th>Today</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $employee)
                        @php
                            $today = $employee->attendance->first();
                            $meta = $today ? \App\Models\StaffAttendance::STATUSES[$today->status] : null;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('society.staff.show', $employee) }}" style="font-weight: 600;">{{ $employee->name }}</a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $employee->employee_code }}</div>
                            </td>
                            <td>{{ $employee->role }}</td>
                            <td>{{ $employee->shiftLabel() }}</td>
                            <td>{{ $employee->mobile }}</td>
                            <td>{{ $employee->employment_type === 'agency' ? ($employee->vendor?->name ?? 'Agency') : 'Society' }}</td>
                            <td>
                                @if($meta)
                                    <span class="badge {{ $meta[2] }}">{{ $meta[0] }}</span>
                                @else
                                    <span class="badge badge-secondary">Not marked</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.staff.show', $employee) }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('society.staff.edit', $employee) }}" class="action-btn edit" title="Edit"><i class="fas fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-tie"></i></div>
                                    <div class="empty-state-title">No staff found</div>
                                    <div class="empty-state-text">Add guards, housekeeping and maintenance staff to track their attendance.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $staff, 'unit' => 'staff'])
    </div>
</div>
@endsection
