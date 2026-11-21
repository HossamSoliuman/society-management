@extends('society.layouts.app')

@section('title', 'Edit Notice')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Notice</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.notices.manage') }}">Manage Notices</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $notice->title }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('society.notices.destroy', $notice) }}" data-confirm="Delete this notice? Residents will no longer see it." data-confirm-label="Delete">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
        </form>
    </div>
</div>

@include('society.notices._form', [
    'formAction' => route('society.notices.update', $notice),
    'isEdit' => true,
])
@endsection
