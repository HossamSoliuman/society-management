@extends('society.layouts.app')

@section('title', 'Society Profile')

@php
    $addressParts = array_filter([
        $society->address_line_1,
        $society->address_line_2,
        trim(($society->city ?? '').($society->pincode ? ' – '.$society->pincode : '')),
    ]);
    $fullAddress = implode(', ', $addressParts);
    $photoUrl = $society->photo_path
        ? asset('storage/'.$society->photo_path)
        : 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80';
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Society Profile</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.profile') }}">Society Management</a>
                <span class="breadcrumb-separator">/</span>
                <span>Society Profile</span>
            </div>
        </div>
        <a href="{{ route('society.profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-pencil"></i> Edit Profile
        </a>
    </div>
</div>

{{-- Row 1 --}}
<div class="content-grid" style="margin-bottom: 20px;">
    {{-- Society overview --}}
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 260px 1fr; gap: 24px;">
                <div>
                    <img src="{{ $photoUrl }}" alt="{{ $society->name }}"
                        style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--radius-md); display: block;">
                    <a href="{{ route('society.profile.edit') }}" class="btn btn-outline-primary" style="width: 100%; margin-top: 12px;">
                        <i class="fas fa-cloud-arrow-up"></i> Upload Logo / Photo
                    </a>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary);">{{ $society->name }}</h2>
                        <span class="status-badge {{ $society->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($society->status) }}</span>
                    </div>

                    @php
                        $overviewRows = [
                            ['fa-barcode', 'Society Code', $society->society_code],
                            ['fa-file-lines', 'Registration Number', $society->registration_number],
                            ['fa-house', 'Society Type', $society->societyType->name ?? 'Residential'],
                            ['fa-building', 'Building Type', $society->building_type],
                            ['fa-calendar', 'Year of Establishment', $society->year_established],
                            ['fa-layer-group', 'Wings / Blocks', $society->wings_count.' Wings , '.$society->blocks_count.' Blocks'],
                            ['fa-list', 'Total Units', $society->total_units],
                            ['fa-shield-halved', 'RERA Number', $society->rera_number],
                        ];
                    @endphp
                    @foreach($overviewRows as [$icon, $label, $value])
                        <div class="detail-row">
                            <div class="detail-row-icon"><i class="fas {{ $icon }}"></i></div>
                            <div class="detail-row-label">{{ $label }}</div>
                            <div class="detail-row-value">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">Contact Information</div>
        </div>
        <div class="card-body">
            @php
                $contactRows = [
                    ['fa-location-dot', 'Address', $fullAddress],
                    ['fa-phone', 'Phone', $society->primary_mobile],
                    ['fa-envelope', 'Email', $society->primary_email],
                    ['fa-globe', 'Website', $society->website],
                    ['fa-clock', 'Office Timings', $society->office_timings],
                ];
            @endphp
            @foreach($contactRows as [$icon, $label, $value])
                <div style="display: flex; gap: 12px; padding: 10px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }}">
                    <div class="detail-row-icon" style="margin-top: 2px;"><i class="fas {{ $icon }}"></i></div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 2px;">{{ $label }}</div>
                        <div style="font-size: 13px; font-weight: 500; color: var(--text-primary);">{{ $value }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Row 2 --}}
<div class="grid-3">
    {{-- Administrative Details --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Administrative Details</div>
        </div>
        <div class="card-body">
            @php
                $adminRows = [
                    ['fa-user-gear', 'Management Type', $society->management_type],
                    ['fa-building-columns', 'Bank Name', $society->bank_name],
                    ['fa-users', 'Managing Committee', $society->committee_members_count.' Members'],
                    ['fa-credit-card', 'Account Number', $society->account_number],
                    ['fa-file-contract', 'Audit Type', $society->audit_type],
                    ['fa-hashtag', 'IFSC Code', $society->ifsc_code],
                    ['fa-calendar-days', 'Financial Year', $society->financial_year],
                    ['fa-id-card', 'PAN Number', $society->pan_number],
                    ['fa-indian-rupee-sign', 'Maintenance Collection Day', $society->maintenance_collection_day],
                    ['fa-receipt', 'GST Number', $society->gst_number],
                ];
            @endphp
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px;">
                @foreach($adminRows as [$icon, $label, $value])
                    <div style="display: flex; gap: 10px;">
                        <div class="detail-row-icon" style="margin-top: 2px;"><i class="fas {{ $icon }}"></i></div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 2px;">{{ $label }}</div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $value }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Amenities --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Amenities</div>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @foreach(($society->amenities ?? []) as $amenity)
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-circle-check" style="color: var(--primary); font-size: 15px;"></i>
                        <span style="font-size: 13px; color: var(--text-primary);">{{ $amenity }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Important Documents --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Important Documents</div>
            <a href="{{ route('society.placeholder', ['page' => 'Documents']) }}" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding: 8px 20px;">
            @foreach($society->documents as $document)
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }}">
                    <i class="fas fa-file-pdf" style="color: var(--danger); font-size: 22px;"></i>
                    <div style="flex: 1;">
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $document->title }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Uploaded on {{ optional($document->uploaded_at)->format('d M Y') }}</div>
                    </div>
                    <a href="{{ $document->file_path ? asset('storage/'.$document->file_path) : '#' }}" class="action-btn view"><i class="fas fa-download"></i></a>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Row 3 — About Society --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">About Society</div>
    </div>
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 24px;">
            <div style="flex: 1;">
                @foreach(preg_split('/\n\n+/', (string) $society->about) as $paragraph)
                    <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.7; margin-bottom: 12px;">{{ trim($paragraph) }}</p>
                @endforeach
            </div>
            <div style="flex-shrink: 0; opacity: 0.18;">
                <svg width="200" height="120" viewBox="0 0 200 120" fill="none" stroke="var(--primary)" stroke-width="2">
                    <rect x="20" y="40" width="40" height="70"/>
                    <rect x="70" y="20" width="50" height="90"/>
                    <rect x="130" y="50" width="45" height="60"/>
                    <line x1="10" y1="110" x2="190" y2="110"/>
                    <rect x="28" y="50" width="10" height="10"/><rect x="42" y="50" width="10" height="10"/>
                    <rect x="28" y="68" width="10" height="10"/><rect x="42" y="68" width="10" height="10"/>
                    <rect x="80" y="32" width="12" height="12"/><rect x="98" y="32" width="12" height="12"/>
                    <rect x="80" y="54" width="12" height="12"/><rect x="98" y="54" width="12" height="12"/>
                    <rect x="80" y="76" width="12" height="12"/><rect x="98" y="76" width="12" height="12"/>
                    <rect x="140" y="62" width="11" height="11"/><rect x="156" y="62" width="11" height="11"/>
                    <rect x="140" y="82" width="11" height="11"/><rect x="156" y="82" width="11" height="11"/>
                </svg>
            </div>
        </div>
    </div>
</div>
@endsection
