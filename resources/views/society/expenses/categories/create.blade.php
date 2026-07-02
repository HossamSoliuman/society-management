@extends('society.layouts.app')

@section('title', 'Add New Category')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Category</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.categories.index') }}">Expense Categories</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New Category</span>
            </div>
        </div>
        <a href="{{ route('society.expenses.categories.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Categories</a>
    </div>
</div>

@include('society.expenses.categories._form', [
    'category' => null,
    'mode' => 'create',
    'action' => route('society.expenses.categories.store'),
])
@endsection
