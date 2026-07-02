@extends('society.layouts.app')

@section('title', 'Edit Asset')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Asset</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.assets.index') }}">Assets Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit Asset</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.assets.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Assets</a>
            <button type="submit" form="assetForm" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Update Asset</button>
        </div>
    </div>
</div>

@include('society.assets._form')
@endsection
