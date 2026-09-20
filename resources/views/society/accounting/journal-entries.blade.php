@extends('society.layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Journal Entries</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Journal Entries</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.journal-entries.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Journal Entry</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body" style="padding-bottom: 0;">
                @include('society.accounting._tabs', ['active' => $active])
            </div>
        </div>

        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-book"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Entries</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Debit</div>
                    <div class="stat-value">&#8377; {{ $stats['total_debit'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Credit</div>
                    <div class="stat-value">&#8377; {{ $stats['total_credit'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>This Month</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Posted Entries</div>
                    <div class="stat-value">{{ $stats['posted'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>Balanced</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('society.accounting.journal-entries') }}" style="margin-bottom: 16px;">
                    <div class="header-search" style="max-width: 360px;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search entry no. or narration...">
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Entry No.</th>
                                <th>Date</th>
                                <th>Narration</th>
                                <th class="num" style="text-align: center;">Lines</th>
                                <th class="num" style="text-align: right;">Debit (&#8377;)</th>
                                <th class="num" style="text-align: right;">Credit (&#8377;)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                                <tr>
                                    <td style="font-weight: 600; white-space: nowrap;">{{ $entry->entry_no }}</td>
                                    <td style="white-space: nowrap;">{{ $entry->date?->format('d M Y') }}</td>
                                    <td>{{ $entry->narration ?? '—' }}</td>
                                    <td style="text-align: center;">{{ $entry->lines_count }}</td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $entry->total_debit, 2) }}</td>
                                    <td style="text-align: right; font-weight: 600;">{{ number_format((float) $entry->total_credit, 2) }}</td>
                                    <td><span class="badge {{ $entry->statusBadgeClass() }}">{{ $entry->statusLabel() }}</span></td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('society.accounting.journal-entries') }}" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                            <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-book"></i></div>
                                            <div class="empty-state-title">No journal entries found</div>
                                            <div class="empty-state-text">Post your first journal entry to get started.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('society.partials.pagination', ['items' => $entries, 'firstLast' => false, 'side' => 2, 'unit' => 'entries'])
            </div>
        </div>
    </div>

    <div>
        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add Journal Entry', 'desc' => 'Post a new entry', 'url' => route('society.accounting.journal-entries.create'), 'color' => 'purple'],
            ['icon' => 'fa-scale-balanced', 'label' => 'Trial Balance', 'desc' => 'Debit / credit summary', 'url' => route('society.accounting.trial-balance'), 'color' => 'green'],
            ['icon' => 'fa-sitemap', 'label' => 'Chart of Accounts', 'desc' => 'Browse all accounts', 'url' => route('society.accounting.chart-of-accounts'), 'color' => 'blue'],
            ['icon' => 'fa-file-lines', 'label' => 'View Transactions', 'desc' => 'All ledger movements', 'url' => route('society.accounting.transactions'), 'color' => 'orange'],
        ]])

        <div class="info-box">
            <i class="fas fa-circle-info"></i>
            <span>Journal entries follow the double-entry principle — every entry has equal debits and credits.</span>
        </div>
    </div>
</div>
@endsection
