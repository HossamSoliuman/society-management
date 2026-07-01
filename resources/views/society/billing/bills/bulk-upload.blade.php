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
                <a href="{{ route('society.billing.bills.index') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <span>Bulk Upload</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('society.billing.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Bills</a>
            <a href="{{ route('society.billing.bulk-upload.sample') }}" class="btn btn-outline-secondary"><i class="fas fa-download"></i> Download Template</a>
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
                <div style="font-size: 11px; color: var(--text-muted);">Upload your Excel/CSV file</div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-label">Review &amp; Validate</div>
                <div style="font-size: 11px; color: var(--text-muted);">Validate bill data</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-label">Generate Bills</div>
                <div style="font-size: 11px; color: var(--text-muted);">Create the bills</div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    {{-- Left: Upload --}}
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div>
                <div class="card-title">Upload File</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Upload your Excel/CSV file containing maintenance bill details.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('society.billing.bills.bulk.store') }}" enctype="multipart/form-data" id="bulkForm">
                @csrf
                <label class="file-upload" for="billFile" style="display: block; padding: 48px 32px;">
                    <i class="fas fa-file-arrow-up" style="color: var(--primary); font-size: 40px;"></i>
                    <div class="file-upload-text" style="font-size: 14px; margin-top: 12px;">Click to upload or drag and drop</div>
                    <div class="file-upload-hint" style="margin-top: 8px;">XLSX / CSV (Max 5MB)</div>
                    <input type="file" name="file" id="billFile" accept=".xlsx,.xls,.csv" style="display: none;" onchange="document.getElementById('bulkForm').submit()">
                </label>
                @error('file')
                    <div style="color: var(--danger); font-size: 12px; margin-top: 8px;">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;"><i class="fas fa-circle-check"></i> Upload &amp; Validate</button>
            </form>
        </div>
    </div>

    {{-- Right: Sample format --}}
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header"><div class="card-title">Sample Format</div></div>
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
                            ['Bill Date', true, 'Bill generation date (DD/MM/YYYY)'],
                            ['Due Date', true, 'Payment due date (DD/MM/YYYY)'],
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

<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 20px; padding-bottom: 24px;">
    <div class="info-box" style="flex: 1; margin: 0;">
        <i class="fas fa-circle-info"></i>
        <span>Upload a file to preview parsed rows with per-row validation before generating bills.</span>
    </div>
    <button class="btn btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">Next: Review &amp; Validate <i class="fas fa-arrow-right"></i></button>
</div>
@endsection
