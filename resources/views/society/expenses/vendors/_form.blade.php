@php
    /**
     * @var \App\Models\Vendor|null $vendor
     * @var string $action
     * @var string $mode           'create' | 'edit'
     * @var \Illuminate\Support\Collection $categories
     */
    $isEdit = ($mode ?? 'create') === 'edit';
    $statusValue = old('status', $vendor?->status ?? 'active');
@endphp

<form method="POST" action="{{ $action }}">
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

    <div class="content-grid">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-store"></i></div>
                        <div><div class="title" style="font-size: 16px;">Vendor Information</div></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Vendor Name <span class="required">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $vendor?->name) }}" class="form-control" placeholder="Enter vendor name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" value="{{ old('company', $vendor?->company) }}" class="form-control" placeholder="Enter company name (optional)">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $vendor?->phone) }}" class="form-control" placeholder="Enter phone number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $vendor?->email) }}" class="form-control" placeholder="Enter email address">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" value="{{ old('gst_number', $vendor?->gst_number) }}" class="form-control" placeholder="Enter GST number (optional)">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) old('category_id', $vendor?->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Enter vendor address (optional)">{{ old('address', $vendor?->address) }}</textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ $statusValue === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $statusValue === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="form-text">Inactive vendors will not be available while adding expenses.</div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Notes <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Enter notes about this vendor">{{ old('notes', $vendor?->notes) }}</textarea>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <a href="{{ route('society.expenses.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Vendor' : 'Save Vendor' }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            <div class="tips-card">
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">Guidelines</div>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li><i class="fas fa-circle-check"></i><span>Use the vendor's registered business name for clarity.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Add a valid GST number to keep expense records compliant.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Link the vendor to a category to speed up expense entry.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Inactive vendors stay in records but hide from new expenses.</span></li>
                </ul>
            </div>
        </div>
    </div>
</form>
