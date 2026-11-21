@extends('society.layouts.app')

@section('title', $tenant->name)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $tenant->name }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.tenants.index') }}">Tenants</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $tenant->name }}</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 10px;">
            <a href="{{ route('society.tenants.edit', $tenant) }}" class="btn btn-secondary"><i class="fas fa-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('society.tenants.destroy', $tenant) }}" data-confirm="Delete the record for {{ $tenant->name }}? Their agreements are deleted too." data-confirm-label="Delete">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>

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

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 0;">Tenant Details</div>
                    <span class="badge {{ $tenant->statusBadgeClass() }}">{{ $tenant->statusLabel() }}</span>
                </div>
                <div class="review-row"><span class="review-label">Unit</span><span class="review-value">
                    @if($tenant->unit)<a href="{{ route('society.units.show', $tenant->unit) }}">{{ $tenant->unit->unit_number }}</a>@else — @endif
                </span></div>
                <div class="review-row"><span class="review-label">Owner</span><span class="review-value">
                    @if($tenant->owner)<a href="{{ route('society.members.show', $tenant->owner) }}">{{ $tenant->owner->name }}</a>@else — @endif
                </span></div>
                <div class="review-row"><span class="review-label">Mobile</span><span class="review-value">{{ $tenant->mobile }}</span></div>
                <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $tenant->email ?? '—' }}</span></div>
                <div class="review-row"><span class="review-label">Occupants</span><span class="review-value">{{ $tenant->occupants_count }}</span></div>
                <div class="review-row"><span class="review-label">ID Proof</span><span class="review-value">{{ $tenant->id_proof_type ? $tenant->id_proof_type.' · '.($tenant->id_proof_number ?? '—') : '—' }}</span></div>
                <div class="review-row"><span class="review-label">Police Verification</span><span class="review-value"><span class="badge {{ $tenant->verificationBadgeClass() }}">{{ $tenant->verificationLabel() }}</span></span></div>
                <div class="review-row"><span class="review-label">Moved In</span><span class="review-value">{{ $tenant->move_in_date?->format('d M Y') ?? '—' }}</span></div>
                @if($tenant->move_out_date)
                    <div class="review-row"><span class="review-label">Moved Out</span><span class="review-value">{{ $tenant->move_out_date->format('d M Y') }}</span></div>
                @endif
                <div class="review-row"><span class="review-label">Permanent Address</span><span class="review-value">{{ $tenant->permanent_address ?? '—' }}</span></div>
                @if($tenant->notes)
                    <div style="margin-top: 12px; font-size: 13px; color: var(--text-secondary); white-space: pre-line;">{{ $tenant->notes }}</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px;">Agreements</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Rent</th>
                                <th>Deposit</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenant->agreements as $agreement)
                                <tr>
                                    <td style="white-space: nowrap;">
                                        {{ $agreement->start_date->format('d M Y') }} – {{ $agreement->end_date->format('d M Y') }}
                                        @if($agreement->agreement_number)<div style="font-size: 11px; color: var(--text-muted);">{{ $agreement->agreement_number }}</div>@endif
                                    </td>
                                    <td>{{ format_inr($agreement->monthly_rent, 0) }}</td>
                                    <td>{{ format_inr($agreement->security_deposit, 0) }}</td>
                                    <td><span class="badge {{ $agreement->stateBadgeClass() }}">{{ $agreement->stateLabel() }}</span></td>
                                    <td>
                                        <div style="display: inline-flex; gap: 6px;">
                                            @if($agreement->document_path)
                                                <a href="{{ route('society.tenants.agreements.download', $agreement) }}" class="action-btn view" title="Download copy"><i class="fas fa-download"></i></a>
                                            @endif
                                            @if($agreement->status === 'active')
                                                <form method="POST" action="{{ route('society.tenants.agreements.terminate', $agreement) }}" data-confirm="Terminate this agreement now?" data-confirm-label="Terminate">
                                                    @csrf
                                                    <button type="submit" class="action-btn edit" title="Terminate"><i class="fas fa-ban"></i></button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('society.tenants.agreements.destroy', $agreement) }}" data-confirm="Delete this agreement record?" data-confirm-label="Delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="color: var(--text-muted);">No agreements recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        @if($tenant->isActive())
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Add Agreement</div>
                    <form method="POST" action="{{ route('society.tenants.agreements.store', $tenant) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Start <span class="required">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">End <span class="required">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Rent (₹) <span class="required">*</span></label>
                                <input type="number" step="0.01" min="0" name="monthly_rent" class="form-control" value="{{ old('monthly_rent', $tenant->agreements->first()?->monthly_rent) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Deposit (₹)</label>
                                <input type="number" step="0.01" min="0" name="security_deposit" class="form-control" value="{{ old('security_deposit', $tenant->agreements->first()?->security_deposit) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Agreement Number</label>
                            <input type="text" name="agreement_number" class="form-control" value="{{ old('agreement_number') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Copy</label>
                            <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-plus"></i> Add Agreement</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Move Out</div>
                    <form method="POST" action="{{ route('society.tenants.move-out', $tenant) }}" data-confirm="Mark {{ $tenant->name }} as moved out? Running agreements end and the unit becomes vacant." data-confirm-label="Move Out">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Move-out Date</label>
                            <input type="date" name="move_out_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <button type="submit" class="btn btn-secondary" style="width: 100%;"><i class="fas fa-door-open"></i> Mark as Moved Out</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
