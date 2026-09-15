@php
    /**
     * @var \App\Models\FamilyMember|null $familyMember
     * @var array<int, string> $relations
     */
    $familyMember = $familyMember ?? null;
@endphp
@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Full name <span class="required">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $familyMember?->name) }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label class="form-label">Relation <span class="required">*</span></label>
        <select name="relation" class="form-control" required>
            <option value="">Select</option>
            @foreach($relations as $relation)
                <option value="{{ $relation }}" {{ old('relation', $familyMember?->relation) === $relation ? 'selected' : '' }}>{{ $relation }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control">
            <option value="">Select</option>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" {{ old('gender', $familyMember?->gender) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Date of birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $familyMember?->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Mobile</label>
        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $familyMember?->mobile) }}" maxlength="30">
    </div>
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $familyMember?->email) }}" maxlength="255">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Occupation</label>
        <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $familyMember?->occupation) }}" maxlength="255">
    </div>
    <div class="form-group">
        <label class="form-label">Lives in the flat?</label>
        <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
            <input type="hidden" name="is_resident" value="0">
            <input type="checkbox" name="is_resident" value="1" {{ old('is_resident', $familyMember?->is_resident ?? true) ? 'checked' : '' }}>
            <span>Yes, resides here</span>
        </label>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">ID proof type</label>
        <input type="text" name="id_proof_type" class="form-control" value="{{ old('id_proof_type', $familyMember?->id_proof_type) }}" maxlength="50" placeholder="Aadhaar, PAN, Passport…">
    </div>
    <div class="form-group">
        <label class="form-label">ID proof number</label>
        <input type="text" name="id_proof_number" class="form-control" value="{{ old('id_proof_number', $familyMember?->id_proof_number) }}" maxlength="100">
    </div>
</div>
<div class="form-group">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="2" maxlength="500">{{ old('notes', $familyMember?->notes) }}</textarea>
</div>
