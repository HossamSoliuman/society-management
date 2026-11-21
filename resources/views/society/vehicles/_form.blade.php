@php
    /**
     * @var \App\Models\Vehicle $vehicle
     * @var string $formAction
     * @var bool $isEdit
     */
    $isEdit = $isEdit ?? false;
@endphp
<form method="POST" action="{{ $formAction }}">
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
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Registration Number <span class="required">*</span></label>
                    <input type="text" name="registration_no" class="form-control" value="{{ old('registration_no', $vehicle->registration_no) }}" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type <span class="required">*</span></label>
                    <select name="vehicle_type" class="form-control" required>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('vehicle_type', $vehicle->vehicle_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active" @selected(old('status', $vehicle->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $vehicle->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Make</label>
                    <input type="text" name="make" class="form-control" value="{{ old('make', $vehicle->make) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Colour</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color) }}">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <select name="unit_id" class="form-control">
                        <option value="">Select unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id', $vehicle->unit_id) === (string) $unit->id)>{{ $unit->unit_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Resident</label>
                    <select name="member_id" class="form-control">
                        <option value="">Select resident</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('member_id', $vehicle->member_id) === (string) $member->id)>{{ $member->name }}{{ $member->flat_unit ? ' · '.$member->flat_unit : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Owner Name</label>
                    <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $vehicle->owner_name) }}" placeholder="Defaults to the resident">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Sticker / RFID Tag</label>
                    <input type="text" name="rfid_tag" class="form-control" value="{{ old('rfid_tag', $vehicle->rfid_tag) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes', $vehicle->notes) }}">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('society.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> {{ $isEdit ? 'Save Changes' : 'Register Vehicle' }}</button>
            </div>
        </div>
    </div>
</form>
