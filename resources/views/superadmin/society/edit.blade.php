@extends('superadmin.layouts.app')

@section('title', 'Edit Society')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Society</h1>
            <p class="page-subtitle">{{ $society->name }} · {{ $society->prefix }}</p>
        </div>
        <div class="action-toolbar-right">
            <a href="{{ route('superadmin.societies.show', $society) }}" class="btn btn-secondary"><i class="fas fa-eye"></i> View</a>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.societies.index') }}">Society Management</a>
        <span class="breadcrumb-separator">/</span>
        <span>Edit</span>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    </div>
@endif

@php
    $text = fn (string $name, string $label, string $type = 'text', bool $required = false) => '
        <div class="form-group">
            <label class="form-label">'.$label.($required ? ' <span class="required">*</span>' : '').'</label>
            <input type="'.$type.'" name="'.$name.'" class="form-control" value="'.e(old($name, $society->{$name} instanceof \Carbon\Carbon ? $society->{$name}->toDateString() : $society->{$name})).'"'.($required ? ' required' : '').'>
        </div>';
@endphp

<form action="{{ route('superadmin.societies.update', $society) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid-3">
        <div style="grid-column: span 2;">
            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-building"></i> Basic Information</div>
                    <div class="form-row">
                        {!! $text('name', 'Society Name', 'text', true) !!}
                        {!! $text('registration_number', 'Registration Number') !!}
                    </div>
                    <div class="form-row">
                        {!! $text('prefix', 'Prefix', 'text', true) !!}
                        <div class="form-group">
                            <label class="form-label">Society Type <span class="required">*</span></label>
                            <select name="society_type_id" class="form-control" required>
                                @foreach($societyTypes as $type)
                                    <option value="{{ $type->id }}" {{ (string) old('society_type_id', $society->society_type_id) === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        {!! $text('registration_date', 'Registration Date', 'date') !!}
                        {!! $text('pan_number', 'PAN Number') !!}
                    </div>
                    <div class="form-row-3">
                        {!! $text('flats_count', 'Flats', 'number') !!}
                        {!! $text('shops_count', 'Shops', 'number') !!}
                        {!! $text('offices_count', 'Offices', 'number') !!}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-location-dot"></i> Contact Details</div>
                    <div class="form-row">
                        {!! $text('address_line_1', 'Address Line 1') !!}
                        {!! $text('address_line_2', 'Address Line 2') !!}
                    </div>
                    <div class="form-row-3">
                        {!! $text('city', 'City') !!}
                        {!! $text('state', 'State') !!}
                        {!! $text('pincode', 'Pincode') !!}
                    </div>
                    <div class="form-row">
                        {!! $text('primary_email', 'Primary Email', 'email') !!}
                        {!! $text('secondary_email', 'Secondary Email', 'email') !!}
                    </div>
                    <div class="form-row-3">
                        {!! $text('primary_mobile', 'Primary Mobile') !!}
                        {!! $text('alternate_mobile', 'Alternate Mobile') !!}
                        {!! $text('landline', 'Landline') !!}
                    </div>
                    {!! $text('website', 'Website', 'url') !!}
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-user-tie"></i> Officials</div>
                    @foreach(['chairman' => 'Chairman', 'secretary' => 'Secretary', 'treasurer' => 'Treasurer'] as $key => $label)
                        <div class="form-row-3">
                            {!! $text($key.'_name', $label.' Name') !!}
                            {!! $text($key.'_mobile', $label.' Mobile') !!}
                            {!! $text($key.'_email', $label.' Email', 'email') !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-toggle-on"></i> Status</div>
                    <div class="form-group">
                        <label class="form-label">Society Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ old('status', $society->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $society->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="form-text">Deactivating locks every user of the society; reactivating restores them.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $society->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-file-contract"></i> Subscription</div>
                    <div class="form-group">
                        <label class="form-label">Current Plan</label>
                        <input type="text" class="form-control" value="{{ $society->subscriptionPlan?->name ?? '—' }}" disabled>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start</label>
                            <input type="text" class="form-control" value="{{ $society->subscription_start_date?->format('d M Y') ?? '—' }}" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End</label>
                            <input type="text" class="form-control" value="{{ $society->subscription_end_date?->format('d M Y') ?? '—' }}" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subscription Status</label>
                        <input type="text" class="form-control" value="{{ ucwords(str_replace('_', ' ', $society->subscription_status)) }}" disabled>
                    </div>
                    <div class="form-row">
                        {!! $text('grace_period_days', 'Grace Period (days)', 'number') !!}
                        <div class="form-group">
                            <label class="form-label">Auto Renewal</label>
                            <select name="auto_renewal" class="form-control">
                                <option value="1" {{ old('auto_renewal', $society->auto_renewal ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('auto_renewal', $society->auto_renewal ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                    @php($current = $society->subscriptions->where('status', '!=', 'cancelled')->sortByDesc('end_date')->first())
                    @if($current)
                        <a href="{{ route('superadmin.subscription.subscriptions.renew', $current) }}" class="btn btn-secondary btn-sm" style="width: 100%;"><i class="fas fa-rotate"></i> Renew / Upgrade</a>
                    @else
                        <a href="{{ route('superadmin.subscription.subscriptions.create') }}" class="btn btn-secondary btn-sm" style="width: 100%;"><i class="fas fa-plus"></i> Add Subscription</a>
                    @endif
                    <div class="form-text" style="margin-top: 8px;">Plan and dates are managed from Subscription Management; they sync here automatically.</div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('superadmin.societies.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Society</button>
            </div>
        </div>
    </div>
</form>
@endsection
