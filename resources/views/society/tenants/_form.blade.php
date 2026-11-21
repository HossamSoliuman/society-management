@php
    /**
     * @var \App\Models\Tenant $tenant
     * @var \Illuminate\Support\Collection $units
     * @var \Illuminate\Support\Collection $owners
     * @var array<int, string> $idProofs
     * @var array<string, string> $verifications
     * @var string $formAction
     * @var bool $isEdit
     */
    $isEdit = $isEdit ?? false;
@endphp
<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

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

    <div class="card">
        <div class="card-body">
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Tenant Details</div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mobile <span class="required">*</span></label>
                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $tenant->mobile) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $tenant->email) }}">
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Unit <span class="required">*</span></label>
                    <select name="unit_id" class="form-control" required>
                        <option value="">Select unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id', $tenant->unit_id) === (string) $unit->id)>{{ $unit->unit_number }}{{ $unit->wing ? ' · '.$unit->wing : '' }}{{ $unit->status === 'vacant' ? ' (vacant)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Owner</label>
                    <select name="owner_member_id" class="form-control">
                        <option value="">Select owner</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((string) old('owner_member_id', $tenant->owner_member_id) === (string) $owner->id)>{{ $owner->name }}{{ $owner->flat_unit ? ' · '.$owner->flat_unit : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Occupants <span class="required">*</span></label>
                    <input type="number" name="occupants_count" class="form-control" min="1" max="50" value="{{ old('occupants_count', $tenant->occupants_count ?? 1) }}" required>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">ID Proof</label>
                    <select name="id_proof_type" class="form-control">
                        <option value="">Select</option>
                        @foreach($idProofs as $proof)
                            <option value="{{ $proof }}" @selected(old('id_proof_type', $tenant->id_proof_type) === $proof)>{{ $proof }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ID Proof Number</label>
                    <input type="text" name="id_proof_number" class="form-control" value="{{ old('id_proof_number', $tenant->id_proof_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Police Verification <span class="required">*</span></label>
                    <select name="police_verification" class="form-control" required>
                        @foreach($verifications as $value => $label)
                            <option value="{{ $value }}" @selected(old('police_verification', $tenant->police_verification) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Move-in Date <span class="required">*</span></label>
                    <input type="date" name="move_in_date" class="form-control" value="{{ old('move_in_date', optional($tenant->move_in_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Permanent Address</label>
                    <input type="text" name="permanent_address" class="form-control" value="{{ old('permanent_address', $tenant->permanent_address) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $tenant->notes) }}</textarea>
            </div>
        </div>
    </div>

    @unless($isEdit)
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Rental Agreement</div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="agreement_start_date" class="form-control" value="{{ old('agreement_start_date') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="agreement_end_date" class="form-control" value="{{ old('agreement_end_date') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Agreement Number</label>
                        <input type="text" name="agreement_number" class="form-control" value="{{ old('agreement_number') }}">
                    </div>
                </div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Monthly Rent (₹)</label>
                        <input type="number" step="0.01" min="0" name="monthly_rent" class="form-control" value="{{ old('monthly_rent') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Security Deposit (₹)</label>
                        <input type="number" step="0.01" min="0" name="security_deposit" class="form-control" value="{{ old('security_deposit') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Agreement Copy</label>
                        <input type="file" name="agreement_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>
        </div>
    @endunless

    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <a href="{{ $isEdit ? route('society.tenants.show', $tenant) : route('society.tenants.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> {{ $isEdit ? 'Save Changes' : 'Add Tenant' }}</button>
    </div>
</form>
