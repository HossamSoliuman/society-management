@extends('superadmin.layouts.app')

@section('title', 'Edit ' . $plan->name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Subscription Plan</h1>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.subscription.plans.show', $plan) }}" class="btn btn-secondary"><i class="fas fa-eye"></i> View Plan</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.plans') }}">Subscription Management</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.subscription.plans.show', $plan) }}">{{ $plan->name }}</a>
        <span class="breadcrumb-separator">/</span>
        <span>Edit</span>
    </div>
</div>

<form action="{{ route('superadmin.subscription.plans.update', $plan) }}" method="POST">
    @csrf
    @method('PUT')
    @include('superadmin.subscription._plan-form', ['plan' => $plan])

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-bottom: 40px;">
        <a href="{{ route('superadmin.subscription.plans.show', $plan) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Subscription Plan</button>
    </div>
</form>
@endsection
