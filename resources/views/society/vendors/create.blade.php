@extends('society.layouts.app')

@section('title', 'Add New Vendor')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Vendor</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.vendors.index') }}">Vendor Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New Vendor</span>
            </div>
        </div>
    </div>
</div>

@include('society.vendors._form', [
    'formAction' => route('society.vendors.store'),
    'isEdit' => false,
])
@endsection
