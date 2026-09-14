@extends('society.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit User</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.settings.users.index') }}">Roles &amp; Permissions</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $user->name }}</span>
            </div>
        </div>
        <a href="{{ route('society.settings.users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

@include('society.settings.users._form', [
    'user' => $user,
    'mode' => 'edit',
    'action' => route('society.settings.users.update', $user),
])
@endsection
