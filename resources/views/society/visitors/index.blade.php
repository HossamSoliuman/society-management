@extends('society.layouts.app')

@section('title', 'Visitor Logs')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Visitor Logs</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Visitors</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 10px;">
            <a href="{{ route('society.visitors.pre-approvals') }}" class="btn btn-secondary"><i class="fas fa-user-check"></i> Pre-Approvals</a>
            <a href="{{ route('society.visitors.create') }}" class="btn btn-primary"><i class="fas fa-right-to-bracket"></i> Check In Visitor</a>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-door-open', 'iconVariant' => 'success', 'label' => 'Inside Now', 'value' => number_format($stats['inside'])])
    @include('society.partials.stat-card', ['icon' => 'fa-users', 'iconVariant' => 'peach', 'label' => 'Visitors Today', 'value' => number_format($stats['today'])])
    @include('society.partials.stat-card', ['icon' => 'fa-box', 'iconVariant' => 'info', 'label' => 'Deliveries Today', 'value' => number_format($stats['deliveries'])])
    @include('society.partials.stat-card', ['icon' => 'fa-user-clock', 'iconVariant' => 'purple', 'label' => 'Expected Today', 'value' => number_format($stats['expected'])])
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.visitors.index') }}" style="display: grid; grid-template-columns: 1fr 170px {{ $view === 'all' ? '150px 150px' : '' }} auto; gap: 12px; align-items: end; margin-bottom: 16px;">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, mobile, vehicle or unit...">
                    </div>
                    <select name="purpose" class="form-control">
                        <option value="">All Purposes</option>
                        @foreach($purposes as $value => $meta)
                            <option value="{{ $value }}" @selected(request('purpose') === $value)>{{ $meta[0] }}</option>
                        @endforeach
                    </select>
                    @if($view === 'all')
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="From">
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}" title="To">
                    @endif
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                </form>

                <div class="tabs">
                    @foreach($views as $key => $label)
                        <a href="{{ route('society.visitors.index', ['view' => $key]) }}" class="tab {{ $view === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Visitor</th>
                                <th>Purpose</th>
                                <th>Unit</th>
                                <th>Checked In</th>
                                <th>Duration</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visits as $visit)
                                <tr>
                                    <td>
                                        <span style="font-weight: 600;">{{ $visit->name }}</span>
                                        @if($visit->preApproval)<span class="badge badge-info" style="margin-left: 4px;">Pass</span>@endif
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            {{ $visit->mobile ?? 'No mobile' }}{{ $visit->people_count > 1 ? ' · '.$visit->people_count.' people' : '' }}{{ $visit->vehicle_no ? ' · '.$visit->vehicle_no : '' }}
                                        </div>
                                    </td>
                                    <td><i class="fas {{ $visit->purposeIcon() }}" style="color: var(--text-muted); width: 16px;"></i> {{ $visit->purposeLabel() }}{{ $visit->company ? ' · '.$visit->company : '' }}</td>
                                    <td>{{ $visit->unit?->unit_number ?? '—' }}</td>
                                    <td style="white-space: nowrap;">{{ $visit->check_in_at->format('d M, h:i A') }}</td>
                                    <td style="white-space: nowrap;">
                                        {{ $visit->durationLabel() }}
                                        @if(! $visit->isInside())
                                            <div style="font-size: 11px; color: var(--text-muted);">out {{ $visit->check_out_at->format('h:i A') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($visit->isInside())
                                            <form method="POST" action="{{ route('society.visitors.check-out', $visit) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-right-from-bracket"></i> Check Out</button>
                                            </form>
                                        @else
                                            <span class="badge badge-secondary">Left</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-id-badge"></i></div>
                                            <div class="empty-state-title">{{ $view === 'inside' ? 'Nobody is inside right now' : 'No visits found' }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $visits, 'unit' => 'visits'])
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Check In With Pass Code</div>
                <form method="POST" action="{{ route('society.visitors.pre-approvals.verify') }}">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="pass_code" class="form-control" inputmode="numeric" maxlength="6" placeholder="6-digit code" value="{{ old('pass_code') }}" required style="font-size: 18px; letter-spacing: 4px; text-align: center;">
                        @error('pass_code')<div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-shield-halved"></i> Verify &amp; Check In</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
