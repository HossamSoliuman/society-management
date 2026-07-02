@extends('society.layouts.app')

@section('title', 'Add New Asset')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Asset</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.assets.index') }}">Assets Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add New Asset</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.assets.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Assets</a>
            <button type="submit" form="assetForm" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Asset</button>
        </div>
    </div>
</div>

@include('society.assets._form')
@endsection
