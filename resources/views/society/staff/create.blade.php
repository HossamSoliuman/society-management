@extends('society.layouts.app')

@section('title', 'Add Staff')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Staff</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.staff.index') }}">Staff</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Staff</span>
            </div>
        </div>
    </div>
</div>

@include('society.staff._form', ['formAction' => route('society.staff.store'), 'isEdit' => false])
@endsection
