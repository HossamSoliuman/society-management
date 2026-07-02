@extends('society.layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Expense</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.expenses.index') }}">Expenses</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit Expense</span>
            </div>
        </div>
        <a href="{{ route('society.expenses.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Expenses</a>
    </div>
</div>

@include('society.expenses._form', [
    'expense' => $expense,
    'mode' => 'edit',
    'action' => route('society.expenses.update', $expense),
])
@endsection
