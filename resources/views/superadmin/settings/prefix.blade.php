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

<div class="card">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="section-title" style="margin: 0;"><i class="fas fa-tags"></i> Auto-Generated Prefix Settings</div>
            <a href="#" class="btn btn-primary btn-sm" onclick="document.getElementById('addPrefix').style.display='block';"><i class="fas fa-plus"></i> Add Prefix</a>
        </div>

        <div id="addPrefix" style="display: none; margin-bottom: 20px;">
            <form action="{{ route('superadmin.settings.prefix.store') }}" method="POST">
                @csrf
                <div class="card" style="background: var(--gray-50);">
                    <div class="card-body">
                        <div class="form-row-3">
                            <div class="form-group">
                                <label class="form-label">Module <span class="required">*</span></label>
                                <input type="text" name="module" class="form-control" placeholder="e.g. Invoice, Receipt" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Prefix <span class="required">*</span></label>
                                <input type="text" name="prefix" class="form-control" placeholder="e.g. INV, RCPT" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Starting Number <span class="required">*</span></label>
                                <input type="number" name="starting_number" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Number Padding</label>
                            <input type="number" name="padding" class="form-control" value="4" min="1" max="10">
                            <div class="form-text">Number of digits for auto-generated numbers (e.g., 4 = 0001)</div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn btn-primary">Save Prefix</button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('addPrefix').style.display='none';">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Module</th>
                        <th>Prefix</th>
                        <th>Starting Number</th>
                        <th>Current Number</th>
                        <th>Padding</th>
                        <th>Format Example</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prefixes as $prefix)
                    <tr>
                        <td style="padding-left: 20px;">{{ $prefix->module }}</td>
                        <td><span class="prefix-tag">{{ $prefix->prefix }}</span></td>
                        <td>{{ $prefix->starting_number }}</td>
                        <td>{{ $prefix->current_number }}</td>
                        <td>{{ $prefix->padding }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $prefix->prefix }}-{{ str_pad($prefix->current_number, $prefix->padding, '0', STR_PAD_LEFT) }}</td>
                        <td><span class="status-badge {{ $prefix->status }}">{{ ucfirst($prefix->status) }}</span></td>
                        <td><div style="display: flex; gap: 4px;"><button class="action-btn edit"><i class="fas fa-pen"></i></button><button class="action-btn delete"><i class="fas fa-trash"></i></button></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('superadmin.components.pagination', ['items' => $prefixes])
    </div>
</div>
@endsection
