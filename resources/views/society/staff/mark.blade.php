@extends('society.layouts.app')

@section('title', 'Mark Attendance')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Mark Attendance</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.staff.attendance') }}">Attendance</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $date->format('d M Y') }}</span>
            </div>
        </div>
        <form method="GET" action="{{ route('society.staff.attendance.mark') }}" style="display: inline-flex; gap: 8px; align-items: center;">
            <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" onchange="this.form.submit()">
        </form>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

<form method="POST" action="{{ route('society.staff.attendance.store') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                <div class="section-title" style="font-size: 16px; margin-bottom: 0;">{{ $date->format('l, d F Y') }}</div>
                <button type="button" class="btn btn-secondary btn-sm" data-mark-all="present"><i class="fas fa-check-double"></i> Mark everyone present</button>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $employee)
                            @php $row = $existing[$employee->id] ?? null; @endphp
                            <tr>
                                <td>
                                    <span style="font-weight: 600;">{{ $employee->name }}</span>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $employee->role }}</div>
                                </td>
                                <td>{{ $employee->shiftLabel() }}</td>
                                <td>
                                    <select name="attendance[{{ $employee->id }}][status]" class="form-control" data-status-select style="min-width: 130px;">
                                        <option value="">Not marked</option>
                                        @foreach($statuses as $value => $meta)
                                            <option value="{{ $value }}" @selected(old("attendance.{$employee->id}.status", $row?->status) === $value)>{{ $meta[0] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="time" name="attendance[{{ $employee->id }}][check_in]" class="form-control" value="{{ old("attendance.{$employee->id}.check_in", $row?->check_in ? substr($row->check_in, 0, 5) : '') }}"></td>
                                <td><input type="time" name="attendance[{{ $employee->id }}][check_out]" class="form-control" value="{{ old("attendance.{$employee->id}.check_out", $row?->check_out ? substr($row->check_out, 0, 5) : '') }}"></td>
                                <td><input type="text" name="attendance[{{ $employee->id }}][notes]" class="form-control" value="{{ old("attendance.{$employee->id}.notes", $row?->notes) }}" maxlength="255"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-user-tie"></i></div>
                                        <div class="empty-state-title">No active staff</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staff->isNotEmpty())
                <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Attendance</button>
                </div>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-mark-all]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-mark-all');
            document.querySelectorAll('[data-status-select]').forEach(function (select) {
                if (!select.value) { select.value = value; }
            });
        });
    });
</script>
@endpush
