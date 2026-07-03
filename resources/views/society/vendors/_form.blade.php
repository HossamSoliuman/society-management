@php
    /**
     * @var \App\Models\ServiceVendor $vendor
     * @var array<int, string> $categories
     * @var array<int, string> $paymentTerms
     * @var array<int, string> $states
     * @var string $formAction   route URL for the form
     * @var bool   $isEdit        whether this is an update form
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

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 20px;">
        <a href="{{ route('society.vendors.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Vendors</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Vendor</button>
    </div>

    {{-- 1. Basic Information --}}
    <div class="card">
        <div class="card-body">
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">1. Basic Information</div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Vendor Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $vendor->name) }}" class="form-control" placeholder="Enter vendor name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Vendor Code</label>
                    <input type="text" name="vendor_code" value="{{ old('vendor_code', $vendor->vendor_code) }}" class="form-control" placeholder="Auto-generated if left blank">
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', $vendor->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $vendor->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ old('category', $vendor->category) === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Person <span class="required">*</span></label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}" class="form-control" placeholder="Enter contact person name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation', $vendor->designation) }}" class="form-control" placeholder="Enter designation (optional)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}" class="form-control" placeholder="Enter GST number (optional)">
                </div>
                <div class="form-group">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" value="{{ old('pan_number', $vendor->pan_number) }}" class="form-control" placeholder="Enter PAN number (optional)">
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Contact Information --}}
    <div class="card">
        <div class="card-body">
            <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">2. Contact Information</div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Phone <span class="required">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" class="form-control" placeholder="Enter phone number" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alternate Phone</label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone', $vendor->alternate_phone) }}" class="form-control" placeholder="Enter alternate phone number">
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $vendor->email) }}" class="form-control" placeholder="Enter email address" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" value="{{ old('website', $vendor->website) }}" class="form-control" placeholder="Enter website (optional)">
                </div>
                <div class="form-group">
                    <label class="form-label">Address <span class="required">*</span></label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Enter complete address" required>{{ old('address', $vendor->address) }}</textarea>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">City <span class="required">*</span></label>
                    <input type="text" name="city" value="{{ old('city', $vendor->city) }}" class="form-control" placeholder="Enter city" required>
                </div>
                <div class="form-group">
                    <label class="form-label">State <span class="required">*</span></label>
                    <select name="state" class="form-control" required>
                        <option value="">Select state</option>
                        @foreach($states as $state)
                            <option value="{{ $state }}" {{ old('state', $vendor->state) === $state ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pin Code <span class="required">*</span></label>
                    <input type="text" name="pin_code" value="{{ old('pin_code', $vendor->pin_code) }}" class="form-control" placeholder="Enter pin code" required>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Banking + 4. Additional --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px; margin-bottom: 20px;"><i class="fas fa-building-columns"></i> 3. Banking Information</div>
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" class="form-control" placeholder="Enter bank name">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $vendor->account_number) }}" class="form-control" placeholder="Enter account number">
                    </div>
                    <div class="form-group">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $vendor->ifsc_code) }}" class="form-control" placeholder="Enter IFSC code">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Account Holder Name</label>
                    <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $vendor->account_holder_name) }}" class="form-control" placeholder="Enter account holder name">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px; margin-bottom: 20px;"><i class="fas fa-circle-info"></i> 4. Additional Information</div>
                <div class="form-group">
                    <label class="form-label">Services / Goods Provided</label>
                    <textarea name="services_provided" class="form-control" rows="2" placeholder="Enter services or goods provided">{{ old('services_provided', $vendor->services_provided) }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Payment Terms</label>
                        <select name="payment_terms" class="form-control">
                            <option value="">Select payment terms</option>
                            @foreach($paymentTerms as $term)
                                <option value="{{ $term }}" {{ old('payment_terms', $vendor->payment_terms) === $term ? 'selected' : '' }}>{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Credit Limit (&#8377;)</label>
                        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $vendor->credit_limit) }}" class="form-control" placeholder="Enter credit limit (optional)">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes', $vendor->notes) }}" class="form-control" placeholder="Enter any additional notes (optional)">
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Documents (static) --}}
    <div class="card" style="margin-top: 16px;">
        <div class="card-body">
            <div class="section-title" style="font-size: 16px; margin-bottom: 6px;">5. Documents (Optional)</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Upload supporting documents like GST certificate, PAN card, etc.</div>
            <div style="border: 2px dashed var(--border); border-radius: 10px; padding: 28px; display: flex; align-items: center; justify-content: center; gap: 16px; color: var(--text-muted);">
                <i class="fas fa-cloud-arrow-up" style="font-size: 28px; color: var(--info);"></i>
                <div>
                    <div><strong>Drag &amp; drop files here</strong> or <span style="color: var(--primary); font-weight: 600;">Choose Files</span></div>
                    <div style="font-size: 12px;">JPG, PNG, PDF up to 5MB</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
        <a href="{{ route('society.vendors.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Vendor</button>
    </div>
</form>
