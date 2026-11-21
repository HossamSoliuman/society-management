@extends('society.layouts.app')

@section('title', 'Visitor Pre-Approvals')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Visitor Pre-Approvals</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.visitors.index') }}">Visitors</a>
                <span class="breadcrumb-separator">/</span>
                <span>Pre-Approvals</span>
            </div>
        </div>
        <a href="{{ route('society.visitors.index') }}" class="btn btn-secondary"><i class="fas fa-list"></i> Visitor Logs</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
                    <div class="tabs" style="margin-bottom: 0;">
                        @foreach($views as $key => $label)
                            <a href="{{ route('society.visitors.pre-approvals', ['view' => $key]) }}" class="tab {{ $view === $key ? 'active' : '' }}">{{ $label }}{{ $key === 'upcoming' && $expectedToday ? ' ('.$expectedToday.' today)' : '' }}</a>
                        @endforeach
                    </div>
                    <form method="GET" action="{{ route('society.visitors.pre-approvals') }}">
                        <input type="hidden" name="view" value="{{ $view }}">
                        <div class="header-search" style="max-width: 260px;">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, mobile or code...">
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Visitor</th>
                                <th>Unit</th>
                                <th>Expected</th>
                                <th>Code</th>
                                <th>Issued By</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($passes as $pass)
                                <tr>
                                    <td>
                                        <span style="font-weight: 600;">{{ $pass->visitor_name }}</span>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $pass->purposeLabel() }}{{ $pass->people_count > 1 ? ' · '.$pass->people_count.' people' : '' }}{{ $pass->mobile ? ' · '.$pass->mobile : '' }}</div>
                                    </td>
                                    <td>{{ $pass->unit?->unit_number ?? '—' }}</td>
                                    <td style="white-space: nowrap;">
                                        {{ $pass->expected_at->format('d M, h:i A') }}
                                        <div style="font-size: 11px; color: var(--text-muted);">valid till {{ $pass->valid_until->format('d M, h:i A') }}</div>
                                    </td>
                                    <td><code style="font-size: 14px; letter-spacing: 2px;">{{ $pass->pass_code }}</code></td>
                                    <td>{{ $pass->member?->name ?? $pass->creator?->name ?? '—' }}<div style="font-size: 11px; color: var(--text-muted);">{{ $pass->member ? 'Resident' : 'Office' }}</div></td>
                                    <td><span class="badge {{ $pass->statusBadgeClass() }}">{{ $pass->statusLabel() }}</span></td>
                                    <td>
                                        @if($pass->displayStatus() === 'pending')
                                            <div style="display: inline-flex; gap: 6px;">
                                                <form method="POST" action="{{ route('society.visitors.pre-approvals.check-in', $pass) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-right-to-bracket"></i> Check In</button>
                                                </form>
                                                <form method="POST" action="{{ route('society.visitors.pre-approvals.cancel', $pass) }}" data-confirm="Cancel the pass for {{ $pass->visitor_name }}?" data-confirm-label="Cancel Pass">
                                                    @csrf
                                                    <button type="submit" class="action-btn delete" title="Cancel"><i class="fas fa-ban"></i></button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-user-check"></i></div>
                                            <div class="empty-state-title">No passes here</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $passes, 'unit' => 'passes'])
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">New Pass</div>
                <form method="POST" action="{{ route('society.visitors.pre-approvals.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Visitor Name <span class="required">*</span></label>
                        <input type="text" name="visitor_name" class="form-control" value="{{ old('visitor_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">Select unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>{{ $unit->unit_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Purpose</label>
                            <select name="purpose" class="form-control">
                                @foreach($purposes as $value => $meta)
                                    <option value="{{ $value }}" @selected(old('purpose', 'guest') === $value)>{{ $meta[0] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">People</label>
                            <input type="number" name="people_count" class="form-control" min="1" max="100" value="{{ old('people_count', 1) }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Arrival <span class="required">*</span></label>
                        <input type="datetime-local" name="expected_at" class="form-control" value="{{ old('expected_at', now()->addHour()->format('Y-m-d\TH:00')) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vehicle Number</label>
                        <input type="text" name="vehicle_no" class="form-control" value="{{ old('vehicle_no') }}" style="text-transform: uppercase;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-ticket"></i> Create Pass</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
