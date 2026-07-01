@extends('society.layouts.app')

@section('title', $page)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $page }}</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('society.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>{{ $page }}</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-screwdriver-wrench"></i></div>
            <div class="empty-state-title">{{ $page }} — Coming Soon</div>
            <div class="empty-state-text">This module will be available in an upcoming phase. Thank you for your patience.</div>
            <a href="{{ route('society.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
