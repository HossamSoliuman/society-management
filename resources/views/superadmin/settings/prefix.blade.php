@extends('superadmin.layouts.app')

@section('title', 'Prefix Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Prefix Settings</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>System Settings</span>
    </div>
</div>

<div class="sub-nav-tabs">
    <a href="{{ route('superadmin.settings.company-profile') }}" class="sub-nav-tab">Company Profile</a>
    <a href="{{ route('superadmin.settings.prefix') }}" class="sub-nav-tab active">Prefix Settings</a>
    <a href="{{ route('superadmin.settings.smtp') }}" class="sub-nav-tab">SMTP Settings</a>
    <a href="{{ route('superadmin.settings.backup') }}" class="sub-nav-tab">Backup Settings</a>
    <a href="{{ route('superadmin.settings.security') }}" class="sub-nav-tab">Security Settings</a>
</div>

<div class="card" style="margin-bottom: 40px;">
    <div class="card-body">
        <div class="section-title"><i class="fas fa-tags"></i> Platform Document Numbering</div>

        @foreach($prefixes as $prefix)
            <form id="prefix-form-{{ $prefix->id }}" action="{{ route('superadmin.settings.prefix.update', $prefix) }}" method="POST">
                @csrf
                @method('PUT')
            </form>
        @endforeach

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Module</th>
                        <th>Prefix</th>
                        <th>Next Number</th>
                        <th>Digits</th>
                        <th>Next Number Preview</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prefixes as $prefix)
                        @php
                            $bag = $errors->getBag('prefix'.$prefix->id);
                        @endphp
                        <tr>
                            <td style="padding-left: 20px; font-weight: 600;">{{ $prefix->module }}</td>
                            <td>
                                <input type="text" name="prefix" form="prefix-form-{{ $prefix->id }}" class="form-control {{ $bag->has('prefix') ? 'is-invalid' : '' }}" value="{{ $bag->any() ? old('prefix', $prefix->prefix) : $prefix->prefix }}" maxlength="20" required style="max-width: 120px;">
                                @if($bag->has('prefix'))<div class="invalid-feedback">{{ $bag->first('prefix') }}</div>@endif
                            </td>
                            <td>
                                <input type="number" name="next_number" form="prefix-form-{{ $prefix->id }}" class="form-control {{ $bag->has('next_number') ? 'is-invalid' : '' }}" value="{{ $bag->any() ? old('next_number', $prefix->nextNumber()) : $prefix->nextNumber() }}" min="1" required style="max-width: 130px;">
                                @if($bag->has('next_number'))<div class="invalid-feedback">{{ $bag->first('next_number') }}</div>@endif
                            </td>
                            <td>
                                <input type="number" name="padding" form="prefix-form-{{ $prefix->id }}" class="form-control {{ $bag->has('padding') ? 'is-invalid' : '' }}" value="{{ $bag->any() ? old('padding', $prefix->padding) : $prefix->padding }}" min="1" max="10" required style="max-width: 90px;">
                                @if($bag->has('padding'))<div class="invalid-feedback">{{ $bag->first('padding') }}</div>@endif
                            </td>
                            <td><span class="prefix-tag">{{ $prefix->format($prefix->nextNumber()) }}</span></td>
                            <td style="text-align: right;"><button type="submit" form="prefix-form-{{ $prefix->id }}" class="btn btn-secondary btn-sm"><i class="fas fa-save"></i> Save</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
