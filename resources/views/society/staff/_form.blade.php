@php
    /**
     * @var \App\Models\Staff $employee
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
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Employment</div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span class="required">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">Select role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $employee->role) === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Employee Code</label>
                    <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code) }}" placeholder="Auto-generated if blank">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Employed By <span class="required">*</span></label>
                    <select name="employment_type" class="form-control" required>
                        @foreach($employmentTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('employment_type', $employee->employment_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Agency</label>
                    <select name="vendor_id" class="form-control">
                        <option value="">Not applicable</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $employee->vendor_id) === (string) $vendor->id)>{{ $vendor->name }}{{ $vendor->category ? ' · '.$vendor->category : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Shift <span class="required">*</span></label>
                    <select name="shift" class="form-control" required>
                        @foreach($shifts as $value => $label)
                            <option value="{{ $value }}" @selected(old('shift', $employee->shift) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Salary (₹)</label>
                    <input type="number" step="0.01" min="0" name="monthly_salary" class="form-control" value="{{ old('monthly_salary', $employee->monthly_salary) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $employee->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Contact &amp; Identity</div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Mobile <span class="required">*</span></label>
                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $employee->mobile) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $employee->emergency_contact) }}" placeholder="Name and number">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">ID Proof</label>
                    <select name="id_proof_type" class="form-control">
                        <option value="">Select</option>
                        @foreach($idProofs as $proof)
                            <option value="{{ $proof }}" @selected(old('id_proof_type', $employee->id_proof_type) === $proof)>{{ $proof }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ID Proof Number</label>
                    <input type="text" name="id_proof_number" class="form-control" value="{{ old('id_proof_number', $employee->id_proof_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $employee->address) }}">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $employee->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <a href="{{ $isEdit ? route('society.staff.show', $employee) : route('society.staff.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> {{ $isEdit ? 'Save Changes' : 'Add Staff' }}</button>
    </div>
</form>
