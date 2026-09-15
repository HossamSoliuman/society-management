@extends('member.layouts.app')

@section('title', 'Family Members')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Family Members</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Family Members</span>
            </div>
        </div>
        <a href="{{ route('member.family.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Family Member</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Name</th>
                        <th>Relation</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Mobile</th>
                        <th>Resident</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($familyMembers as $family)
                        <tr>
                            <td style="padding-left: 20px; font-weight: 600;">{{ $family->name }}</td>
                            <td>{{ $family->relation ?: '—' }}</td>
                            <td>{{ $family->gender ? ucfirst($family->gender) : '—' }}</td>
                            <td>{{ $family->date_of_birth?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $family->mobile ?: '—' }}</td>
                            <td><span class="badge {{ $family->is_resident ? 'badge-success' : 'badge-secondary' }}">{{ $family->is_resident ? 'Yes' : 'No' }}</span></td>
                            <td style="text-align: right; padding-right: 20px;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('member.family.edit', $family) }}" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil"></i></a>
                                    <form method="POST" action="{{ route('member.family.destroy', $family) }}" data-confirm="Remove {{ $family->name }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">No family members added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
