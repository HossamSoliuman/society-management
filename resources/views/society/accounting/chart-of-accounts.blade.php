@extends('society.layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Chart of Accounts</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <span>Chart of Accounts</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.accounting.chart-of-accounts') }}" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-file-import"></i> Import Accounts</a>
            <a href="{{ route('society.accounting.chart-of-accounts') }}" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
            <a href="{{ route('society.accounting.chart-of-accounts.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Account</a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div>
        {{-- Stat cards --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Accounts</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>All Accounts</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-folder"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Active Accounts</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-trend" style="color: var(--success);"><span>{{ $stats['active_pct'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactive Accounts</div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-trend" style="color: var(--warning);"><span>{{ $stats['inactive_pct'] }}</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-indian-rupee-sign"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Groups</div>
                    <div class="stat-value">{{ $stats['groups'] }}</div>
                    <div class="stat-trend" style="color: var(--text-muted);"><span>Account Groups</span></div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card">
            <div class="card-body">
                <div class="tabs">
                    <a href="{{ route('society.accounting.chart-of-accounts') }}" class="tab {{ $tab === 'list' ? 'active' : '' }}">Account List</a>
                    <a href="{{ route('society.accounting.chart-of-accounts', ['tab' => 'groups']) }}" class="tab {{ $tab === 'groups' ? 'active' : '' }}">Account Groups</a>
                </div>

                @if($tab === 'list')
                    {{-- Filter bar --}}
                    <form method="GET" action="{{ route('society.accounting.chart-of-accounts') }}" style="margin-bottom: 16px;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 14px; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="header-search" style="max-width: none;">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search accounts...">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <select name="group" class="form-control">
                                    <option value="">All Groups</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    @foreach($accountTypes as $val => $label)
                                        <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-secondary" style="color: var(--info); border-color: var(--info);"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                    </form>

                    {{-- Hierarchical table --}}
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Group</th>
                                    <th>Type</th>
                                    <th class="num" style="text-align: right;">Balance (&#8377;)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $account)
                                    <tr>
                                        <td style="white-space: nowrap;">{{ $account->tree_no ?? ($accounts->firstItem() + $loop->index) }}</td>
                                        <td style="font-weight: 600;">{{ $account->code }}</td>
                                        <td>
                                            <span class="coa-name coa-indent-{{ min($account->indent ?? 0, 3) }}">
                                                @if($account->type === 'group')
                                                    <i class="fas fa-chevron-right coa-toggle"></i>
                                                @else
                                                    <span class="coa-toggle"></span>
                                                @endif
                                                <span>{{ $account->name }}</span>
                                            </span>
                                        </td>
                                        <td>{{ $account->group?->name ?? '—' }}</td>
                                        <td><span class="{{ $account->typePillClass() }}">{{ ucfirst($account->type) }}</span></td>
                                        <td style="text-align: right; font-weight: 600;">{{ (float) $account->balance > 0 ? number_format((float) $account->balance, 2) : '—' }}</td>
                                        <td><span class="badge {{ $account->statusBadgeClass() }}">{{ $account->statusLabel() }}</span></td>
                                        <td>
                                            <div style="display: inline-flex; gap: 6px;">
                                                <a href="{{ route('society.accounting.chart-of-accounts.create') }}" class="action-btn edit" title="Edit" style="color: var(--warning); border-color: var(--warning);"><i class="fas fa-pencil"></i></a>
                                                <button type="button" class="action-btn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <div class="empty-state-icon"><i class="fas fa-sitemap"></i></div>
                                                <div class="empty-state-title">No accounts found</div>
                                                <div class="empty-state-text">Try adjusting your filters or add a new account.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @include('society.partials.pagination', ['items' => $accounts, 'firstLast' => false, 'side' => 2, 'unit' => 'accounts'])
                @else
                    {{-- Account Groups grid --}}
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        @foreach($groups as $group)
                            @php [$bg, $fg] = \App\Models\AssetCategory::tint($group->color ?? 'gray'); @endphp
                            <div class="card" style="margin-bottom: 0;">
                                <div class="card-body">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                        <span class="rli-ico" style="width: 40px; height: 40px; font-size: 16px; background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $group->icon ?? 'fa-layer-group' }}"></i></span>
                                        <div>
                                            <div style="font-weight: 700;">{{ $group->name }}</div>
                                            <div style="font-size: 12px; color: var(--text-muted);">{{ $group->accounts_count }} accounts</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ route('society.accounting.chart-of-accounts', ['group' => $group->id]) }}" class="btn btn-secondary btn-sm">View</a>
                                        <a href="{{ route('society.accounting.chart-of-accounts.create') }}" class="btn btn-secondary btn-sm">Add Account</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right rail --}}
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div class="section-title" style="font-size: 15px; margin-bottom: 0;">Account Groups</div>
                    <a href="{{ route('society.accounting.chart-of-accounts', ['tab' => 'groups']) }}" style="font-size: 13px; color: var(--primary); font-weight: 600;">View All</a>
                </div>
                @foreach($groups as $group)
                    @php [$bg, $fg] = \App\Models\AssetCategory::tint($group->color ?? 'gray'); @endphp
                    <div class="rail-list-item">
                        <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $group->icon ?? 'fa-layer-group' }}"></i></span>
                        <div class="rli-main"><div class="rli-title">{{ $group->name }}</div></div>
                        <span class="rli-meta">{{ $group->accounts_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @include('society.partials.quick-actions', ['items' => [
            ['icon' => 'fa-plus', 'label' => 'Add Account', 'desc' => 'Create a new account', 'url' => route('society.accounting.chart-of-accounts.create'), 'color' => 'orange'],
            ['icon' => 'fa-layer-group', 'label' => 'Add Account Group', 'desc' => 'Create a new group', 'url' => route('society.accounting.chart-of-accounts.create'), 'color' => 'green'],
            ['icon' => 'fa-pencil', 'label' => 'Edit Account', 'desc' => 'Modify an account', 'url' => route('society.accounting.chart-of-accounts'), 'color' => 'blue'],
            ['icon' => 'fa-trash', 'label' => 'Delete Account', 'desc' => 'Remove an account', 'url' => route('society.accounting.chart-of-accounts'), 'color' => 'red'],
        ]])

        <div class="tips-card">
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: var(--text-primary);"><i class="fas fa-circle-info" style="color: var(--info);"></i> Tips</div>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li><i class="fas fa-check"></i> Group accounts by type for cleaner reports.</li>
                <li><i class="fas fa-check"></i> Use detail accounts for actual transactions.</li>
                <li><i class="fas fa-check"></i> Keep account codes consistent and sequential.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
