@extends('society.layouts.app')

@section('title', 'Add Tenant')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Tenant</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenants.index') }}">Tenants</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Tenant</span>
            </div>
        </div>
    </div>
</div>

@include('society.tenants._form', ['formAction' => route('society.tenants.store'), 'isEdit' => false])
@endsection
