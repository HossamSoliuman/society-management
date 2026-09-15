@extends('member.layouts.app')

@section('title', 'Edit Vehicle')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Vehicle</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.vehicles.index') }}">Vehicles</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $vehicle->registration_no }}</span>
            </div>
        </div>
        <a href="{{ route('member.vehicles.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    <div class="card-body">
        <form method="POST" action="{{ route('member.vehicles.update', $vehicle) }}">
            @csrf @method('PUT')
            @include('member.vehicles._form', ['types' => $types, 'units' => $units, 'vehicle' => $vehicle])
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('member.vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
