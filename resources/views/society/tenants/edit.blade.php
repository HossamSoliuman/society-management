@extends('society.layouts.app')

@section('title', 'Edit Tenant')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Tenant</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenants.index') }}">Tenants</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenants.show', $tenant) }}">{{ $tenant->name }}</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit</span>
            </div>
        </div>
    </div>
</div>

@include('society.tenants._form', ['formAction' => route('society.tenants.update', $tenant), 'isEdit' => true])
@endsection
