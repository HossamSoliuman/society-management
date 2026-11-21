@extends('society.layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">General Settings</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>General Settings</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.settings.general.update') }}">
    @csrf
    @method('PUT')

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @foreach($groups as $group => $settings)
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px;">{{ $group }}</div>
                @foreach($settings as $setting)
                    <div class="setting-toggle-row">
                        <span class="setting-text">
                            <span class="label">{{ $setting['label'] }}</span>
                        </span>
                        @switch($setting['type'])
                            @case('bool')
                                <label class="toggle-switch">
                                    <input type="checkbox" name="{{ $setting['key'] }}" value="1" @checked(old($setting['key'], $setting['value']))>
                                    <span class="toggle-slider"></span>
                                </label>
                                @break
                            @case('month')
                                <select name="{{ $setting['key'] }}" class="form-control" style="width: 180px;">
                                    @foreach(range(1, 12) as $month)
                                        <option value="{{ $month }}" @selected((int) old($setting['key'], $setting['value']) === $month)>{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                                    @endforeach
                                </select>
                                @break
                            @default
                                <input type="number" name="{{ $setting['key'] }}" class="form-control" style="width: 120px;"
                                    min="{{ $setting['min'] ?? 0 }}" max="{{ $setting['max'] ?? '' }}" value="{{ old($setting['key'], $setting['value']) }}">
                        @endswitch
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Settings</button>
    </div>
</form>
@endsection
