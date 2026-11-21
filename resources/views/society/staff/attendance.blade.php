@extends('society.layouts.app')

@section('title', 'Staff Attendance')

@push('styles')
<style>
    .att-grid th, .att-grid td { padding: 6px 4px; text-align: center; font-size: 11px; white-space: nowrap; }
    .att-grid th:first-child, .att-grid td:first-child { text-align: left; position: sticky; left: 0; background: var(--white); z-index: 1; padding-left: 10px; min-width: 170px; }
    .att-cell { display: inline-block; width: 20px; height: 20px; line-height: 20px; border-radius: 4px; font-weight: 700; font-size: 10px; }
    .att-present { background: var(--success-light); color: #065f46; }
    .att-absent { background: var(--danger-light); color: #991b1b; }
    .att-half_day { background: #fef3c7; color: #92400e; }
    .att-leave { background: #dbeafe; color: #1e40af; }
    .att-weekend { background: var(--gray-50); }
</style>
@endpush

@section('content')
@php
    $cssFor = fn (string $status) => 'att-'.$status;
@endphp
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Staff Attendance</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.staff.index') }}">Staff</a>
                <span class="breadcrumb-separator">/</span>
                <span>Attendance</span>
            </div>
        </div>
        <a href="{{ route('society.staff.attendance.mark') }}" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Mark Attendance</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.staff.attendance') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="display: inline-flex; align-items: center; gap: 8px;">
                <a href="{{ route('society.staff.attendance', array_merge(request()->except('month'), ['month' => $month->copy()->subMonthNoOverflow()->format('Y-m')])) }}" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
                <input type="month" name="month" class="form-control" value="{{ $month->format('Y-m') }}" style="width: 170px;" onchange="this.form.submit()">
                <a href="{{ route('society.staff.attendance', array_merge(request()->except('month'), ['month' => $month->copy()->addMonthNoOverflow()->format('Y-m')])) }}" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div style="display: inline-flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                @foreach($statuses as $key => $meta)
                    <span style="font-size: 12px; color: var(--text-secondary);"><span class="att-cell {{ $cssFor($key) }}">{{ $meta[1] }}</span> {{ $meta[0] }}</span>
                @endforeach
                <select name="role" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="data-table att-grid">
                <thead>
                    <tr>
                        <th>Staff</th>
                        @foreach($days as $day)
                            @php $isWeekend = $month->copy()->day($day)->isSunday(); @endphp
                            <th class="{{ $isWeekend ? 'att-weekend' : '' }}">{{ $day }}</th>
                        @endforeach
                        <th>P</th>
                        <th>A</th>
                        <th>H</th>
                        <th>L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $employee)
                        @php $rows = $records[$employee->id] ?? collect(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('society.staff.show', [$employee, 'month' => $month->format('Y-m')]) }}" style="font-weight: 600;">{{ $employee->name }}</a>
                                <div style="font-size: 10px; color: var(--text-muted);">{{ $employee->role }}</div>
                            </td>
                            @foreach($days as $day)
                                @php $record = $rows[$day] ?? null; @endphp
                                <td class="{{ $month->copy()->day($day)->isSunday() ? 'att-weekend' : '' }}">
                                    @if($record)
                                        <span class="att-cell {{ $cssFor($record->status) }}" title="{{ $statuses[$record->status][0] }}">{{ $statuses[$record->status][1] }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td style="font-weight: 600;">{{ $rows->where('status', 'present')->count() }}</td>
                            <td style="font-weight: 600;">{{ $rows->where('status', 'absent')->count() }}</td>
                            <td style="font-weight: 600;">{{ $rows->where('status', 'half_day')->count() }}</td>
                            <td style="font-weight: 600;">{{ $rows->where('status', 'leave')->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 5 }}">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-calendar-check"></i></div>
                                    <div class="empty-state-title">No staff to show</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
