@extends('superadmin.layouts.app')

@section('title', 'Add New Subscription Plan')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Subscription Plan</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.plans') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Add New Plan</span>
    </div>
</div>

<form action="{{ route('superadmin.subscription.plans.store') }}" method="POST">
    @csrf
    @include('superadmin.subscription._plan-form')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-bottom: 40px;">
        <a href="{{ route('superadmin.subscription.plans') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Subscription Plan</button>
    </div>
</form>
@endsection
