@php
    /**
     * @var \App\Models\Vehicle|null $vehicle
     * @var array<string, string> $types
     * @var \Illuminate\Support\Collection<int, \App\Models\Unit> $units
     */
    $vehicle = $vehicle ?? null;
@endphp
@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Registration number <span class="required">*</span></label>
        <input type="text" name="registration_no" class="form-control" value="{{ old('registration_no', $vehicle?->registration_no) }}" required maxlength="30" placeholder="MH 12 AB 1234" style="text-transform: uppercase;">
    </div>
    <div class="form-group">
        <label class="form-label">Vehicle type <span class="required">*</span></label>
        <select name="vehicle_type" class="form-control" required>
            @foreach($types as $value => $label)
                <option value="{{ $value }}" {{ old('vehicle_type', $vehicle?->vehicle_type ?? 'car') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Make</label>
        <input type="text" name="make" class="form-control" value="{{ old('make', $vehicle?->make) }}" maxlength="255" placeholder="Maruti, Honda…">
    </div>
    <div class="form-group">
        <label class="form-label">Model</label>
        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle?->model) }}" maxlength="255">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Colour</label>
        <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle?->color) }}" maxlength="50">
    </div>
    <div class="form-group">
        <label class="form-label">Owner name</label>
        <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $vehicle?->owner_name) }}" maxlength="255" placeholder="Defaults to you">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Unit</label>
        <select name="unit_id" class="form-control">
            <option value="">Not linked</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ (string) old('unit_id', $vehicle?->unit_id) === (string) $unit->id ? 'selected' : '' }}>{{ $unit->unit_number }}{{ $unit->building ? ' · '.$unit->building : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">RFID / sticker tag</label>
        <input type="text" name="rfid_tag" class="form-control" value="{{ old('rfid_tag', $vehicle?->rfid_tag) }}" maxlength="100">
    </div>
</div>
<div class="form-group">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="2" maxlength="500">{{ old('notes', $vehicle?->notes) }}</textarea>
</div>
