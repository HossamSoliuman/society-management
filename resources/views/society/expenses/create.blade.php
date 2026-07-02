@extends('society.layouts.app')

@section('title', 'Add New Expense')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Expense</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New Expense</span>
            </div>
        </div>
    </div>
</div>

@include('society.expenses._form', [
    'expense' => null,
    'mode' => 'create',
    'action' => route('society.expenses.store'),
])
@endsection
