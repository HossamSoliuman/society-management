@extends('society.layouts.app')

@section('title', 'Family Members')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Family Members</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.members.index') }}">Members</a>
                <span class="breadcrumb-separator">/</span>
                <span>Family</span>
            </div>
        </div>
        <a href="{{ route('society.family-members.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Family Member</a>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-people-roof', 'iconVariant' => 'peach', 'label' => 'Family Members', 'value' => number_format($stats['total'])])
    @include('society.partials.stat-card', ['icon' => 'fa-house-user', 'iconVariant' => 'success', 'label' => 'Living In', 'value' => number_format($stats['residents'])])
    @include('society.partials.stat-card', ['icon' => 'fa-house', 'iconVariant' => 'info', 'label' => 'Households', 'value' => number_format($stats['households'])])
    @include('society.partials.stat-card', ['icon' => 'fa-child', 'iconVariant' => 'purple', 'label' => 'Under 18', 'value' => number_format($stats['children'])])
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.family-members.index') }}" style="display: grid; grid-template-columns: 1fr 170px 160px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <div class="header-search" style="max-width: none;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, mobile, member or flat...">
            </div>
            <select name="relation" class="form-control">
                <option value="">All Relations</option>
                @foreach($relations as $relation)
                    <option value="{{ $relation }}" @selected(request('relation') === $relation)>{{ $relation }}</option>
                @endforeach
            </select>
            <select name="resident" class="form-control">
                <option value="">Living in or not</option>
                <option value="yes" @selected(request('resident') === 'yes')>Living in</option>
                <option value="no" @selected(request('resident') === 'no')>Lives elsewhere</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.family-members.index') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Relation</th>
                        <th>Household</th>
                        <th>Age</th>
                        <th>Mobile</th>
                        <th>Living In</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($family as $person)
                        <tr>
                            <td style="font-weight: 600;">{{ $person->name }}<div style="font-size: 11px; color: var(--text-muted); font-weight: 400;">{{ $person->occupation }}</div></td>
                            <td>{{ $person->relation }}</td>
                            <td>
                                @if($person->member)
                                    <a href="{{ route('society.members.show', $person->member) }}">{{ $person->member->name }}</a>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $person->member->flat_unit }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $person->date_of_birth ? $person->date_of_birth->age : '—' }}</td>
                            <td>{{ $person->mobile ?? '—' }}</td>
                            <td><span class="badge {{ $person->is_resident ? 'badge-success' : 'badge-secondary' }}">{{ $person->is_resident ? 'Yes' : 'No' }}</span></td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('society.family-members.edit', $person) }}" class="action-btn edit" title="Edit"><i class="fas fa-pencil"></i></a>
                                    <form method="POST" action="{{ route('society.family-members.destroy', $person) }}" data-confirm="Remove {{ $person->name }} from the household?" data-confirm-label="Remove">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Remove"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-people-roof"></i></div>
                                    <div class="empty-state-title">No family members recorded</div>
                                    <div class="empty-state-text">Residents can add their household from the portal, or add them here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $family, 'unit' => 'family members'])
    </div>
</div>
@endsection
