@extends('society.layouts.app')

@section('title', 'Bulk Upload Bills')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Bulk Upload Bills</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.placeholder', ['page' => 'Maintenance Billing']) }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <span>Bulk Upload</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('society.placeholder', ['page' => 'Bill List']) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Bill List</a>
            <a href="{{ route('society.billing.bulk-upload.sample') }}" class="btn btn-outline-secondary"><i class="fas fa-download"></i> Download Sample</a>
        </div>
    </div>
</div>

{{-- Steps wizard --}}
<div class="card">
    <div class="card-body">
        <div class="steps-wizard" style="margin-bottom: 0;">
            <div class="step-item active">
                <div class="step-number">1</div>
                <div class="step-label">Upload File</div>
                <div style="font-size: 11px; color: var(--text-muted);">Upload your Excel file</div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-label">Map Columns</div>
                <div style="font-size: 11px; color: var(--text-muted);">Map file columns</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-label">Review &amp; Validate</div>
                <div style="font-size: 11px; color: var(--text-muted);">Validate bill data</div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-label">Confirm &amp; Import</div>
                <div style="font-size: 11px; color: var(--text-muted);">Import bills</div>
            </div>
        </div>
    </div>
</div>

{{-- Body: two columns --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    {{-- Left: Upload --}}
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div>
                <div class="card-title">Upload Excel File</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Upload your Excel file containing maintenance bill details.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('society.billing.bulk-upload.store') }}" enctype="multipart/form-data" id="bulkUploadForm">
                @csrf
                <label class="file-upload" for="billFile" style="display: block; padding: 48px 32px;">
                    <i class="fas fa-file-excel" style="color: var(--success); font-size: 40px;"></i>
                    <div class="file-upload-text" style="font-size: 14px; margin-top: 12px;">Drag and drop your Excel file here</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin: 10px 0;">or</div>
                    <span class="btn btn-outline-primary">Choose File</span>
                    <div class="file-upload-hint" style="margin-top: 16px;">Only .xlsx, .xls files are supported. Max file size: 5MB</div>
                    <input type="file" name="file" id="billFile" accept=".xlsx,.xls" style="display: none;" onchange="document.getElementById('bulkUploadForm').submit()">
                </label>
            </form>

            <div class="info-box" style="margin-top: 16px;">
                <i class="fas fa-circle-info"></i>
                <span>Ensure your file follows the required format.
                    <a href="{{ route('society.billing.bulk-upload.sample') }}" style="color: var(--info); font-weight: 600;">Download sample file</a> to get started.
                </span>
            </div>
        </div>
    </div>

    {{-- Right: Instructions + Required Columns --}}
    <div>
        <div class="card">
            <div class="card-header"><div class="card-title">Instructions</div></div>
            <div class="card-body">
                @php
                    $instructions = [
                        'Download the sample Excel file and enter bill details.',
                        'First row should contain column headers.',
                        'Do not change the column headers in the file.',
                        'Date format should be DD/MM/YYYY.',
                        'Amount should be numeric.',
                        'Save the file and upload here.',
                    ];
                @endphp
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($instructions as $instruction)
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fas fa-circle-check" style="color: var(--primary); font-size: 14px; margin-top: 2px;"></i>
                            <span style="font-size: 13px; color: var(--text-secondary);">{{ $instruction }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-header"><div class="card-title">Required Columns</div></div>
            <div class="card-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="padding-left: 20px;">Column Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $columns = [
                                ['Flat No', true, 'Flat / Unit number (e.g. A-101)'],
                                ['Member Mobile', true, 'Registered mobile number'],
                                ['Bill Month', true, 'Bill month (e.g. June 2025)'],
                                ['Bill Date', true, 'Bill generation date'],
                                ['Due Date', true, 'Payment due date'],
                                ['Charge Head', true, 'Maintenance charge name'],
                                ['Amount (₹)', true, 'Amount for the charge'],
                                ['Notes', false, 'Remarks (optional)'],
                            ];
                        @endphp
                        @foreach($columns as [$name, $required, $desc])
                            <tr>
                                <td style="padding-left: 20px; font-weight: 600;">
                                    {{ $name }}@if($required)<span style="color: var(--danger);"> *</span>@endif
                                </td>
                                <td style="color: var(--text-secondary);">{{ $desc }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="padding: 16px 20px;">
                <a href="{{ route('society.billing.bulk-upload.sample') }}" class="btn btn-outline-primary" style="width: 100%;">
                    <i class="fas fa-download"></i> Download Sample File
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Uploaded Files --}}
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <div>
            <div class="card-title">Uploaded Files</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">View your recently uploaded files and their status.</div>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="padding-left: 20px;">File Name</th>
                    <th>Uploaded On</th>
                    <th>Uploaded By</th>
                    <th>Records</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($uploads as $upload)
                    <tr>
                        <td style="padding-left: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-file-excel" style="color: var(--success); font-size: 18px;"></i>
                                <span style="font-weight: 600;">{{ $upload->original_name }}</span>
                            </div>
                        </td>
                        <td>{{ $upload->created_at->format('d M Y, h:i A') }}</td>
                        <td>{{ $upload->uploaded_by }}</td>
                        <td>{{ $upload->records_count }}</td>
                        <td>@include('society.partials.status-badge', ['class' => $upload->statusBadgeClass(), 'label' => ucfirst($upload->status)])</td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-file-excel"></i></div>
                                <div class="empty-state-title">No files uploaded yet</div>
                                <div class="empty-state-text">Upload an Excel file to get started.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Footer bar --}}
<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 20px; padding-bottom: 24px;">
    <div class="info-box" style="flex: 1; margin: 0;">
        <i class="fas fa-circle-info"></i>
        <span>Please validate your data carefully before importing. You will be able to review all records in the next step.</span>
    </div>
    <button class="btn btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">Next: Map Columns <i class="fas fa-arrow-right"></i></button>
</div>
@endsection
