@extends('society.layouts.app')

@section('title', 'New Notice')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">New Notice</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.notices.manage') }}">Manage Notices</a>
                <span class="breadcrumb-separator">/</span>
                <span>New Notice</span>
            </div>
        </div>
    </div>
</div>

@include('society.notices._form', [
    'formAction' => route('society.notices.store'),
    'isEdit' => false,
])
@endsection
