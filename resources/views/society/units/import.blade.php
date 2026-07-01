@extends('society.layouts.app')

@section('title', 'Import Units')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Import Units</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.units.index') }}">Unit Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Import Units</span>
            </div>
        </div>
        <a href="{{ route('society.units.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Units</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
@endif

<div class="card">
    <div class="card-header"><div class="card-title">Upload Excel File</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('society.units.import.store') }}" enctype="multipart/form-data" id="unitImportForm">
            @csrf
            <label class="file-upload" for="unitFile" style="display: block;">
                <i class="fas fa-file-excel" style="color: var(--success);"></i>
                <div class="file-upload-text">Drag and drop your Excel file here, or click to browse</div>
                <div class="file-upload-hint">Only .xlsx, .xls, .csv files are supported. Max file size: 5MB</div>
                <input type="file" name="file" id="unitFile" accept=".xlsx,.xls,.csv" style="display: none;" onchange="this.form.submit()">
            </label>
        </form>
        <div class="info-box" style="margin-top: 16px;">
            <i class="fas fa-circle-info"></i>
            <span>Ensure your file follows the required format with columns: Unit Number, Building, Wing, Floor, Unit Type, Area, Status, Owner Name, Owner Mobile.</span>
        </div>
    </div>
</div>
@endsection
