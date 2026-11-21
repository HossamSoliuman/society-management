@extends('member.layouts.app')

@section('title', 'Visitors')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Visitors</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Visitors</span>
            </div>
        </div>
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
                <div class="section-title" style="font-size: 16px;">My Visitor Passes</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Visitor</th>
                                <th>Expected</th>
                                <th>Code</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($passes as $pass)
                                <tr>
                                    <td>
                                        <span style="font-weight: 600;">{{ $pass->visitor_name }}</span>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $pass->purposeLabel() }}{{ $pass->people_count > 1 ? ' · '.$pass->people_count.' people' : '' }}</div>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $pass->expected_at->format('d M, h:i A') }}</td>
                                    <td><code style="font-size: 15px; letter-spacing: 2px;">{{ $pass->pass_code }}</code></td>
                                    <td><span class="badge {{ $pass->statusBadgeClass() }}">{{ $pass->statusLabel() }}</span></td>
                                    <td>
                                        @if($pass->displayStatus() === 'pending')
                                            <form method="POST" action="{{ route('member.visitors.cancel', $pass) }}" data-confirm="Cancel the pass for {{ $pass->visitor_name }}?" data-confirm-label="Cancel Pass">
                                                @csrf
                                                <button type="submit" class="action-btn delete" title="Cancel"><i class="fas fa-ban"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="color: var(--text-muted);">No passes yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px;">Recent Visits{{ $unit ? ' to '.$unit->unit_number : '' }}</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Visitor</th>
                                <th>Purpose</th>
                                <th>In</th>
                                <th>Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visits as $visit)
                                <tr>
                                    <td style="font-weight: 600;">{{ $visit->name }}</td>
                                    <td>{{ $visit->purposeLabel() }}{{ $visit->company ? ' · '.$visit->company : '' }}</td>
                                    <td style="white-space: nowrap;">{{ $visit->check_in_at->format('d M, h:i A') }}</td>
                                    <td style="white-space: nowrap;">{{ $visit->check_out_at?->format('h:i A') ?? 'Inside' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="color: var(--text-muted);">No visits recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        @if($canPreApprove)
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Pre-approve a Visitor</div>
                    <form method="POST" action="{{ route('member.visitors.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Visitor Name <span class="required">*</span></label>
                            <input type="text" name="visitor_name" class="form-control" value="{{ old('visitor_name') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
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
        @else
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span>Visitor passes are not available for your account. Please contact the society office.</span>
            </div>
        @endif
    </div>
</div>
@endsection
