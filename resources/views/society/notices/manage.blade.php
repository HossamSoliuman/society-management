@extends('society.layouts.app')

@section('title', 'Manage Notices')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Manage Notices</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.notices.index') }}">Notices</a>
                <span class="breadcrumb-separator">/</span>
                <span>Manage</span>
            </div>
        </div>
        <a href="{{ route('society.notices.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Notice</a>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-bullhorn', 'iconVariant' => 'peach', 'label' => 'Total Notices', 'value' => number_format($stats['total'])])
    @include('society.partials.stat-card', ['icon' => 'fa-signal', 'iconVariant' => 'success', 'label' => 'Live Now', 'value' => number_format($stats['live'])])
    @include('society.partials.stat-card', ['icon' => 'fa-clock', 'iconVariant' => 'info', 'label' => 'Scheduled', 'value' => number_format($stats['scheduled'])])
    @include('society.partials.stat-card', ['icon' => 'fa-file-pen', 'iconVariant' => 'warning', 'label' => 'Drafts', 'value' => number_format($stats['draft'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.notices.manage') }}" style="display: grid; grid-template-columns: 1fr 180px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by title...">
            </div>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                @foreach(['published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Draft', 'expired' => 'Expired'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.notices.manage') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Audience</th>
                        <th>Publish On</th>
                        <th>Expires</th>
                        <th>Reach</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notices as $notice)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if($notice->pin_to_dashboard)<i class="fas fa-thumbtack" style="color: var(--orange);" title="Pinned"></i>@endif
                                    <span style="font-weight: 600;">{{ $notice->title }}</span>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    <span class="badge {{ $notice->priorityBadgeClass() }}">{{ ucfirst($notice->priority) }}</span>
                                    {{ ucfirst($notice->notice_type) }} · by {{ $notice->creator?->name ?? '—' }}
                                </div>
                            </td>
                            <td>{{ $audiences[$notice->societyAudience()]['label'] }}</td>
                            <td style="white-space: nowrap;">{{ $notice->publish_at?->format('d M Y, h:i A') ?? '—' }}</td>
                            <td style="white-space: nowrap;">{{ $notice->expires_at?->format('d M Y') ?? 'Never' }}</td>
                            <td style="white-space: nowrap;">
                                {{ number_format($notice->status === 'published' ? $notice->delivered_count : $notice->estimated_recipients) }} people
                                @if($notice->require_acknowledgement)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $notice->acknowledgements_count }} acknowledged</div>
                                @endif
                            </td>
                            <td><span class="badge {{ $notice->statusBadgeClass() }}">{{ ucfirst($notice->status) }}</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.notices.edit', $notice) }}" class="action-btn edit" title="Edit"><i class="fas fa-pencil"></i></a>
                                    <form method="POST" action="{{ route('society.notices.destroy', $notice) }}" data-confirm="Delete &quot;{{ $notice->title }}&quot;? Residents will no longer see it." data-confirm-label="Delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-bullhorn"></i></div>
                                    <div class="empty-state-title">No notices yet</div>
                                    <div class="empty-state-text">Write a notice to reach your residents and team.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $notices, 'unit' => 'notices'])
    </div>
</div>
@endsection
