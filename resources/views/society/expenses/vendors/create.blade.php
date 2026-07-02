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
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.vendors.index') }}">Vendors</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New Vendor</span>
            </div>
        </div>
        <a href="{{ route('society.expenses.vendors.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Vendors</a>
    </div>
</div>

@include('society.expenses.vendors._form', [
    'vendor' => null,
    'mode' => 'create',
    'action' => route('society.expenses.vendors.store'),
])
@endsection
