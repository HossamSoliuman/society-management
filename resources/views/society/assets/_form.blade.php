@php
    /**
     * @var \App\Models\Asset|null $asset
     * @var string $action
     * @var string $mode
     * @var string $nextCode
     * @var \Illuminate\Support\Collection $categories
     * @var array $statuses
     * @var array $conditions
     * @var array $towers
     * @var array $floors
     * @var array $depreciationMethods
     * @var array $usageTypes
     */
    $isEdit = ($mode ?? 'create') === 'edit';
    $val = fn ($field, $default = null) => old($field, $asset?->{$field} ?? $default);
    $dateVal = fn ($field) => old($field, optional($asset?->{$field})->format('Y-m-d'));
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="assetForm">
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
            {{-- 1. Asset Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">1. Asset Information</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Asset Name <span class="required">*</span></label>
                            <input type="text" name="name" id="fldName" value="{{ $val('name') }}" class="form-control" placeholder="Enter asset name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Asset ID / Code <span class="required">*</span></label>
                            <input type="text" name="asset_code" value="{{ $val('asset_code', $nextCode) }}" class="form-control" placeholder="e.g. AST0001" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="category_id" id="fldCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) $val('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" value="{{ $val('brand') }}" class="form-control" placeholder="Enter brand">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" value="{{ $val('model') }}" class="form-control" placeholder="Enter model">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" value="{{ $val('serial_number') }}" class="form-control" placeholder="Enter serial number">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter a short description (optional)">{{ $val('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 2. Location & Ownership --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">2. Location &amp; Ownership</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Location <span class="required">*</span></label>
                            <select name="location" id="fldLocation" class="form-control" required>
                                <option value="">Select Location</option>
                                @foreach($towers as $tower)
                                    <option value="{{ $tower }}" {{ $val('location') === $tower ? 'selected' : '' }}>{{ $tower }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tower / Wing</label>
                            <select name="tower_wing" class="form-control">
                                <option value="">Select Tower / Wing</option>
                                @foreach($towers as $tower)
                                    <option value="{{ $tower }}" {{ $val('tower_wing') === $tower ? 'selected' : '' }}>{{ $tower }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Floor</label>
                            <select name="floor" class="form-control">
                                <option value="">Select Floor</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor }}" {{ $val('floor') === $floor ? 'selected' : '' }}>{{ $floor }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Area / Room</label>
                            <input type="text" name="area_room" value="{{ $val('area_room') }}" class="form-control" placeholder="Enter area / room">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assigned To (Staff / Department)</label>
                            <input type="text" name="assigned_to" value="{{ $val('assigned_to') }}" class="form-control" placeholder="Enter staff / department">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Vendor / Supplier</label>
                            <input type="text" name="vendor_supplier" value="{{ $val('vendor_supplier') }}" class="form-control" placeholder="Enter vendor / supplier">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Purchase From</label>
                        <input type="text" name="purchase_from" value="{{ $val('purchase_from') }}" class="form-control" placeholder="Enter purchase source">
                    </div>
                </div>
            </div>

            {{-- 3. Purchase & Warranty Details --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">3. Purchase &amp; Warranty Details</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Purchase Date <span class="required">*</span></label>
                            <input type="date" name="purchase_date" id="fldPurchaseDate" value="{{ $dateVal('purchase_date') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Purchase Cost (&#8377;) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_cost" id="fldCost" value="{{ $val('purchase_cost') }}" class="form-control" placeholder="Enter purchase cost" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Warranty Start Date</label>
                            <input type="date" name="warranty_start" value="{{ $dateVal('warranty_start') }}" class="form-control">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Warranty End Date</label>
                            <input type="date" name="warranty_end" value="{{ $dateVal('warranty_end') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Invoice / Bill No.</label>
                            <input type="text" name="invoice_no" value="{{ $val('invoice_no') }}" class="form-control" placeholder="Enter invoice / bill no.">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Invoice / Bill Date</label>
                            <input type="date" name="invoice_date" value="{{ $dateVal('invoice_date') }}" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Expected Life (Years)</label>
                            <input type="number" min="0" name="expected_life_years" value="{{ $val('expected_life_years') }}" class="form-control" placeholder="Enter expected life in years">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Depreciation Method</label>
                            <select name="depreciation_method" class="form-control">
                                <option value="">Select Method</option>
                                @foreach($depreciationMethods as $method)
                                    <option value="{{ $method }}" {{ $val('depreciation_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Asset Details --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">4. Asset Details</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" id="fldStatus" class="form-control" required>
                                @foreach($statuses as $val_ => $label)
                                    <option value="{{ $val_ }}" {{ $val('status', 'in_use') === $val_ ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Condition <span class="required">*</span></label>
                            <select name="condition" class="form-control" required>
                                @foreach($conditions as $val_ => $label)
                                    <option value="{{ $val_ }}" {{ $val('condition', 'good') === $val_ ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Usage Type</label>
                            <select name="usage_type" class="form-control">
                                <option value="">Select Usage Type</option>
                                @foreach($usageTypes as $usage)
                                    <option value="{{ $usage }}" {{ $val('usage_type') === $usage ? 'selected' : '' }}>{{ $usage }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Asset Tags / QR Code</label>
                        <div style="display: flex; gap: 12px;">
                            <input type="text" name="qr_code" value="{{ $val('qr_code') }}" class="form-control" placeholder="Enter or scan asset QR code">
                            <button type="button" class="btn btn-secondary btn-icon" title="Scan"><i class="fas fa-qrcode"></i></button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter any additional notes (optional)">{{ $val('notes') }}</textarea>
                    </div>

                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 16px;">* Fields marked with an asterisk (*) are required.</div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <a href="{{ route('society.assets.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> {{ $isEdit ? 'Update Asset' : 'Save Asset' }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            {{-- Asset Images --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Asset Images</div>
                    <label class="file-upload" for="assetImages">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <div class="file-upload-text">Drag &amp; drop images here <br> or <br> <span style="color: var(--info); font-weight: 600;">Choose Files</span></div>
                        <div class="file-upload-hint">JPG, PNG, WEBP up to 5MB each. You can upload up to 5 images.</div>
                        <input type="file" id="assetImages" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple style="display: none;">
                    </label>
                    <div id="imageStrip" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 12px;">
                        @for($i = 0; $i < 4; $i++)
                            <div style="aspect-ratio: 1; border-radius: var(--radius-sm); background: var(--gray-100); display: flex; align-items: center; justify-content: center; color: var(--text-muted);"><i class="fas fa-image"></i></div>
                        @endfor
                        <div style="aspect-ratio: 1; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 11px; grid-column: span 4; height: 32px; aspect-ratio: auto;"><i class="fas fa-plus" style="margin-right: 4px;"></i> More</div>
                    </div>
                </div>
            </div>

            {{-- Asset Summary --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Asset Summary</div>
                    <div class="review-row"><span class="review-label"><i class="fas fa-layer-group" style="margin-right: 6px; color: var(--text-muted);"></i>Category</span><span class="review-value" id="sumCategory">Not selected</span></div>
                    <div class="review-row"><span class="review-label"><i class="fas fa-location-dot" style="margin-right: 6px; color: var(--text-muted);"></i>Location</span><span class="review-value" id="sumLocation">Not selected</span></div>
                    <div class="review-row"><span class="review-label"><i class="fas fa-circle-info" style="margin-right: 6px; color: var(--text-muted);"></i>Status</span><span class="review-value" id="sumStatus">&bull; Not selected</span></div>
                    <div class="review-row"><span class="review-label"><i class="fas fa-calendar" style="margin-right: 6px; color: var(--text-muted);"></i>Purchase Date</span><span class="review-value" id="sumDate">Not selected</span></div>
                    <div class="review-row"><span class="review-label"><i class="fas fa-indian-rupee-sign" style="margin-right: 6px; color: var(--text-muted);"></i>Purchase Cost</span><span class="review-value" id="sumCost">&#8377;0.00</span></div>
                    <div class="review-row"><span class="review-label"><i class="fas fa-shield" style="margin-right: 6px; color: var(--text-muted);"></i>Warranty</span><span class="review-value" id="sumWarranty">Not selected</span></div>
                </div>
            </div>

            {{-- Quick Tips --}}
            <div class="tips-card">
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">Quick Tips</div>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li><i class="fas fa-circle-check"></i><span>Use a unique Asset ID for easy tracking.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Upload clear images for better identification.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Regular maintenance extends asset life.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Keep warranty and invoice details updated.</span></li>
                </ul>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const selText = (sel) => (sel && sel.value ? sel.options[sel.selectedIndex].text : 'Not selected');

    function sync() {
        $('sumCategory').textContent = selText($('fldCategory'));
        $('sumLocation').textContent = selText($('fldLocation'));
        $('sumStatus').innerHTML = '&bull; ' + selText($('fldStatus'));
        const date = $('fldPurchaseDate').value;
        $('sumDate').textContent = date ? new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Not selected';
        const cost = parseFloat($('fldCost').value) || 0;
        $('sumCost').innerHTML = '&#8377;' + cost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    ['fldCategory', 'fldLocation', 'fldStatus'].forEach((id) => $(id).addEventListener('change', sync));
    ['fldPurchaseDate', 'fldCost'].forEach((id) => $(id).addEventListener('input', sync));

    $('assetImages').addEventListener('change', function () {
        if (this.files.length) {
            this.closest('.file-upload').querySelector('.file-upload-text').innerHTML = this.files.length + ' file(s) selected';
        }
    });

    sync();
})();
</script>
@endpush
