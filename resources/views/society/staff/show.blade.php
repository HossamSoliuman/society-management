@extends('society.layouts.app')

@section('title', $employee->name)

@section('content')
@php
    $statuses = \App\Models\StaffAttendance::STATUSES;
    $first = $month->copy()->startOfMonth();
    $leading = $first->dayOfWeekIso - 1;
@endphp
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $employee->name }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.staff.index') }}">Staff</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $employee->name }}</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 10px;">
            <a href="{{ route('society.staff.edit', $employee) }}" class="btn btn-secondary"><i class="fas fa-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('society.staff.destroy', $employee) }}" data-confirm="Remove {{ $employee->name }} from the staff list? Attendance history is kept." data-confirm-label="Remove">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Remove</button>
            </form>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 0;">Attendance · {{ $month->format('F Y') }}</div>
                    <div style="display: inline-flex; gap: 8px;">
                        <a href="{{ route('society.staff.show', [$employee, 'month' => $month->copy()->subMonthNoOverflow()->format('Y-m')]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
                        <a href="{{ route('society.staff.show', [$employee, 'month' => $month->copy()->addMonthNoOverflow()->format('Y-m')]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; font-size: 12px;">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                        <div style="color: var(--text-muted); font-weight: 600;">{{ $dayName }}</div>
                    @endforeach
                    @for($i = 0; $i < $leading; $i++)
                        <div></div>
                    @endfor
                    @for($day = 1; $day <= $month->daysInMonth; $day++)
                        @php $record = $records[$day] ?? null; @endphp
                        <div style="border: 1px solid var(--border-color); border-radius: 6px; padding: 6px 2px; min-height: 48px;">
                            <div style="font-weight: 600;">{{ $day }}</div>
                            @if($record)
                                <span class="badge {{ $statuses[$record->status][2] }}" style="font-size: 10px;">{{ $statuses[$record->status][1] }}</span>
                            @endif
                        </div>
                    @endfor
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px; font-size: 13px;">
                    @foreach($statuses as $key => $meta)
                        <span><span class="badge {{ $meta[2] }}">{{ $meta[1] }}</span> {{ $meta[0] }}: <strong>{{ $summary[$key] }}</strong></span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Profile</div>
                    <span class="badge {{ $employee->statusBadgeClass() }}">{{ \App\Models\Staff::STATUSES[$employee->status] ?? ucfirst($employee->status) }}</span>
                </div>
                <div class="review-row"><span class="review-label">Code</span><span class="review-value">{{ $employee->employee_code ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Role</span><span class="review-value">{{ $employee->role }}</span></div>
                <div class="review-row"><span class="review-label">Shift</span><span class="review-value">{{ $employee->shiftLabel() }}</span></div>
                <div class="review-row"><span class="review-label">Employed By</span><span class="review-value">{{ $employee->employment_type === 'agency' ? ($employee->vendor?->name ?? 'Agency') : 'Society' }}</span></div>
                <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $employee->mobile }}</span></div>
                <div class="review-row"><span class="review-label">Emergency</span><span class="review-value">{{ $employee->emergency_contact ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">ID Proof</span><span class="review-value">{{ $employee->id_proof_type ? $employee->id_proof_type.' · '.($employee->id_proof_number ?? '—') : '—' }}</span></div>
                <div class="review-row"><span class="review-label">Joined</span><span class="review-value">{{ $employee->joining_date?->format('d M Y') ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Salary</span><span class="review-value">{{ $employee->monthly_salary !== null ? format_inr($employee->monthly_salary, 0) : '—' }}</span></div>
                @if($employee->notes)
                    <div style="margin-top: 12px; font-size: 13px; color: var(--text-secondary); white-space: pre-line;">{{ $employee->notes }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
