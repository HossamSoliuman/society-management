@extends('member.layouts.app')

@section('title', 'Edit Family Member')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Family Member</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.family.index') }}">Family Members</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $familyMember->name }}</span>
            </div>
        </div>
        <a href="{{ route('member.family.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    <div class="card-body">
        <form method="POST" action="{{ route('member.family.update', $familyMember) }}">
            @csrf @method('PUT')
            @include('member.family._form', ['relations' => $relations, 'familyMember' => $familyMember])
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('member.family.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
