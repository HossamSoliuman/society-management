@extends('society.layouts.app')

@section('title', 'Edit Vehicle')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $vehicle->registration_no }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.vehicles.index') }}">Vehicles</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit</span>
            </div>
        </div>
        <form method="POST" action="{{ route('society.vehicles.destroy', $vehicle) }}" data-confirm="Remove {{ $vehicle->registration_no }}? Any parking allotment for it ends today." data-confirm-label="Remove">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Remove</button>
        </form>
    </div>
</div>

@if($vehicle->allotment)
    <div class="info-box" style="margin-bottom: 16px;">
        <i class="fas fa-square-parking"></i>
        <span>Parked in slot {{ $vehicle->allotment->slot?->slot_number }} since {{ $vehicle->allotment->start_date->format('d M Y') }}.</span>
    </div>
@endif

@include('society.vehicles._form', ['formAction' => route('society.vehicles.update', $vehicle), 'isEdit' => true])
@endsection
