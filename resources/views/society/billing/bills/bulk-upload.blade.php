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
            <div class="step-item {{ $preview ? 'completed' : 'active' }}">
                <div class="step-number">1</div>
                <div class="step-label">Upload File</div>
                <div style="font-size: 11px; color: var(--text-muted);">Upload your Excel/CSV file</div>
            </div>
            <div class="step-item {{ $preview ? 'active' : '' }}">
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
            <div class="table-responsive">
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
        </div>
        <div class="card-footer" style="padding: 16px 20px;">
            <a href="{{ route('society.billing.bulk-upload.sample') }}" class="btn btn-outline-primary" style="width: 100%;">
                <i class="fas fa-download"></i> Download Sample File
            </a>
        </div>
    </div>
</div>

@if($preview)
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <div>
                <div class="card-title">Review &amp; Validate — {{ $preview['file_name'] }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    <span class="badge badge-success">{{ count($preview['rows']) }} valid</span>
                    <span class="badge {{ count($preview['errors']) ? 'badge-danger' : 'badge-secondary' }}">{{ count($preview['errors']) }} with errors</span>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <form method="POST" action="{{ route('society.billing.bills.bulk.discard') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-xmark"></i> Discard</button>
                </form>
                <form method="POST" action="{{ route('society.billing.bills.bulk.confirm') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" {{ count($preview['rows']) === 0 ? 'disabled' : '' }}><i class="fas fa-check"></i> Generate {{ count($preview['rows']) }} Row(s)</button>
                </form>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="padding-left: 20px;">Row</th>
                            <th>Flat</th>
                            <th>Bill Month</th>
                            <th>Bill / Due</th>
                            <th>Charge Head</th>
                            <th style="text-align: right;">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preview['errors'] as $bad)
                            <tr style="background: var(--danger-light, #fef2f2);">
                                <td style="padding-left: 20px;">{{ $bad['row'] }}</td>
                                <td>{{ $bad['data']['flat_no'] ?: '—' }}</td>
                                <td>{{ $bad['data']['bill_month'] ?: '—' }}</td>
                                <td>{{ $bad['data']['bill_date'] ?? '—' }} / {{ $bad['data']['due_date'] ?? '—' }}</td>
                                <td>{{ $bad['data']['charge_head'] ?: '—' }}</td>
                                <td style="text-align: right;">{{ $bad['data']['amount'] !== null ? number_format($bad['data']['amount'], 2) : '—' }}</td>
                                <td style="color: var(--danger); font-size: 12px;">{{ implode(' ', $bad['errors']) }}</td>
                            </tr>
                        @endforeach
                        @foreach($preview['rows'] as $row)
                            <tr>
                                <td style="padding-left: 20px;">{{ $row['row'] }}</td>
                                <td>{{ $row['flat_no'] }}</td>
                                <td>{{ $row['bill_month'] }}</td>
                                <td>{{ $row['bill_date'] }} / {{ $row['due_date'] }}</td>
                                <td>{{ $row['charge_head'] }}</td>
                                <td style="text-align: right;">{{ number_format($row['amount'], 2) }}</td>
                                <td><span class="badge badge-success">Valid</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 20px; padding-bottom: 24px;">
        <div class="info-box" style="flex: 1; margin: 0;">
            <i class="fas fa-circle-info"></i>
            <span>Upload a file to preview parsed rows with per-row validation before generating bills.</span>
        </div>
    </div>
@endif
@endsection
