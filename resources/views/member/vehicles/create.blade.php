@extends('member.layouts.app')

@section('title', 'Add Vehicle')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Vehicle</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.vehicles.index') }}">Vehicles</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add</span>
            </div>
        </div>
        <a href="{{ route('member.vehicles.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    <div class="card-body">
        <form method="POST" action="{{ route('member.vehicles.store') }}">
            @csrf
            @include('member.vehicles._form', ['types' => $types, 'units' => $units])
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('member.vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
