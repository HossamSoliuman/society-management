@extends('society.layouts.app')

@section('title', 'Member Requests')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Member Requests</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.members.index') }}">Members</a>
                <span class="breadcrumb-separator">/</span>
                <span>Requests</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; justify-content: space-between;">
        <div style="flex: 1; min-width: 240px;">
            <div style="font-weight: 600; font-size: 14px;">Join link</div>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Share it with new owners and tenants. Their requests land here for approval.</div>
        </div>
        <div style="display: flex; gap: 8px; flex: 1; min-width: 280px;">
            <input type="text" class="form-control" value="{{ $joinUrl }}" readonly id="joinUrl">
            <button type="button" class="btn btn-secondary" data-copy="#joinUrl"><i class="fas fa-copy"></i> Copy</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <div class="tabs" style="margin-bottom: 0;">
                @foreach(\App\Models\MemberRequest::STATUSES as $key => $meta)
                    <a href="{{ route('society.member-requests.index', ['status' => $key]) }}" class="tab {{ $status === $key ? 'active' : '' }}">{{ $meta[0] }} ({{ (int) ($counts[$key] ?? 0) }})</a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('society.member-requests.index') }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="header-search" style="max-width: 260px;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, mobile or unit...">
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Request</th>
                        <th>Unit</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('society.member-requests.show', $item) }}" style="font-weight: 600;">{{ $item->name }}</a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $item->mobile }}{{ $item->email ? ' · '.$item->email : '' }}</div>
                            </td>
                            <td>{{ $item->typeLabel() }}<div style="font-size: 11px; color: var(--text-muted);">as {{ strtolower($item->memberTypeLabel()) }}</div></td>
                            <td>{{ $item->unit_number }}@if(! $item->unit_id && $item->status === 'pending')<div style="font-size: 11px; color: var(--warning);">Not in unit register</div>@endif</td>
                            <td style="white-space: nowrap;">{{ $item->created_at->format('d M Y') }}</td>
                            <td><span class="badge {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>@if($item->reviewer)<div style="font-size: 11px; color: var(--text-muted);">by {{ $item->reviewer->name }}</div>@endif</td>
                            <td><a href="{{ route('society.member-requests.show', $item) }}" class="action-btn view" title="Review"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-clock"></i></div>
                                    <div class="empty-state-title">No {{ strtolower(\App\Models\MemberRequest::STATUSES[$status][0]) }} requests</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $items, 'unit' => 'requests'])
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.querySelector(button.getAttribute('data-copy'));
            if (!input) { return; }
            input.select();
            (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject())
                .then(function () { window.appToast('Join link copied.'); })
                .catch(function () { document.execCommand('copy'); window.appToast('Join link copied.'); });
        });
    });
</script>
@endpush
