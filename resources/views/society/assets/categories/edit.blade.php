@extends('society.layouts.app')

@section('title', 'Edit Asset Category')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Asset Category</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.assets.index') }}">Assets Management</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.assets.categories.index') }}">Asset Categories</a>
                <span class="breadcrumb-separator">/</span>
                <span>Edit Asset Category</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 12px;">
            <a href="{{ route('society.assets.categories.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Asset Categories</a>
            <button type="submit" form="assetCategoryForm" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Update Category</button>
        </div>
    </div>
</div>

@include('society.assets.categories._form')
@endsection
