@extends('society.layouts.app')

@section('title', 'Register Vehicle')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Register Vehicle</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.vehicles.index') }}">Vehicles</a>
                <span class="breadcrumb-separator">/</span>
                <span>Register</span>
            </div>
        </div>
    </div>
</div>

@include('society.vehicles._form', ['formAction' => route('society.vehicles.store'), 'isEdit' => false])
@endsection
