@extends('society.layouts.app')

@section('title', $familyMember->exists ? 'Edit Family Member' : 'Add Family Member')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $familyMember->exists ? $familyMember->name : 'Add Family Member' }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.family-members.index') }}">Family Members</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $familyMember->exists ? 'Edit' : 'Add' }}</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ $familyMember->exists ? route('society.family-members.update', $familyMember) : route('society.family-members.store') }}">
    @csrf
    @if($familyMember->exists)
        @method('PUT')
    @endif

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

    <div class="card">
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Household <span class="required">*</span></label>
                    <select name="member_id" class="form-control" required>
                        <option value="">Select resident</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('member_id', $familyMember->member_id) === (string) $member->id)>{{ $member->name }}{{ $member->flat_unit ? ' · '.$member->flat_unit : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $familyMember->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Relation <span class="required">*</span></label>
                    <select name="relation" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($relations as $relation)
                            <option value="{{ $relation }}" @selected(old('relation', $familyMember->relation) === $relation)>{{ $relation }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender', $familyMember->gender) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($familyMember->date_of_birth)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Occupation</label>
                    <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $familyMember->occupation) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $familyMember->mobile) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $familyMember->email) }}">
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <input type="checkbox" name="is_resident" value="1" @checked(old('is_resident', $familyMember->is_resident))> Lives in the society
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $familyMember->notes) }}</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('society.family-members.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save</button>
            </div>
        </div>
    </div>
</form>
@endsection
