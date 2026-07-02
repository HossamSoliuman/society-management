@php
    /**
     * @var \App\Models\Expense|null $expense
     * @var string $action
     * @var string $mode           'create' | 'edit'
     * @var \Illuminate\Support\Collection $categories
     * @var \Illuminate\Support\Collection $vendors
     * @var array $paymentModes
     * @var array $expenseForOptions
     */
    $isEdit = ($mode ?? 'create') === 'edit';
    $dateValue = old('expense_date', optional($expense?->expense_date)->format('Y-m-d') ?? '2024-05-31');
    $billDateValue = old('bill_date', optional($expense?->bill_date)->format('Y-m-d'));
    $statusValue = old('payment_status', $expense?->payment_status ?? 'paid');
    $descriptionValue = old('description', $expense?->description);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="expenseForm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="save_and_add" id="saveAndAdd" value="0">

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
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Expense Details</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Expense Date <span class="required">*</span></label>
                            <input type="date" name="expense_date" value="{{ $dateValue }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expense Title <span class="required">*</span></label>
                            <input type="text" name="title" id="fldTitle" value="{{ old('title', $expense?->title) }}" class="form-control" placeholder="Enter expense title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="category_id" id="fldCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) old('category_id', $expense?->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Vendor <span class="required">*</span></label>
                            <select name="vendor_id" id="fldVendor" class="form-control" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ (string) old('vendor_id', $expense?->vendor_id) === (string) $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" id="fldRef" value="{{ old('reference_no', $expense?->reference_no) }}" class="form-control" placeholder="Enter reference / bill no.">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Payment Mode <span class="required">*</span></label>
                            <select name="payment_mode" id="fldMode" class="form-control" required>
                                <option value="">Select Payment Mode</option>
                                @foreach($paymentModes as $pmode)
                                    <option value="{{ $pmode }}" {{ old('payment_mode', $expense?->payment_mode) === $pmode ? 'selected' : '' }}>{{ $pmode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount (&#8377;) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" id="fldAmount" value="{{ old('amount', $expense?->amount) }}" class="form-control" placeholder="Enter amount" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tax / GST (&#8377;)</label>
                            <input type="number" step="0.01" min="0" name="tax_amount" id="fldTax" value="{{ old('tax_amount', $expense?->tax_amount) }}" class="form-control" placeholder="Enter tax amount (optional)">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Paid Amount (&#8377;) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="paid_amount" id="fldPaid" value="{{ old('paid_amount', $expense?->paid_amount) }}" class="form-control" placeholder="Enter paid amount" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Amount (&#8377;)</label>
                            <input type="text" id="fldDue" value="0.00" class="form-control" style="background: var(--gray-100);" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bill / Invoice Date</label>
                            <input type="date" name="bill_date" value="{{ $billDateValue }}" class="form-control" placeholder="Select bill date">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Payment Status <span class="required">*</span></label>
                            <select name="payment_status" id="fldStatus" class="form-control" required>
                                @foreach(['paid' => 'Paid', 'pending' => 'Pending', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'] as $val => $label)
                                    <option value="{{ $val }}" {{ $statusValue === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expense For</label>
                            <select name="expense_for" class="form-control">
                                <option value="">Select (Choose Building / Wing / Flat / Society)</option>
                                @foreach($expenseForOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('expense_for', $expense?->expense_for) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" id="fldDescription" class="form-control" rows="3" maxlength="500" placeholder="Enter description or notes (optional)">{{ $descriptionValue }}</textarea>
                        <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="descCount">{{ mb_strlen((string) $descriptionValue) }}</span> / 500</div>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border-color); margin: 8px 0 20px;">

                    <div style="font-size: 15px; font-weight: 700; margin-bottom: 16px;">Additional Information <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">(Optional)</span></div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Attach Bill / Invoice</label>
                            <label class="file-upload" for="attachment">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <div class="file-upload-text">Click to upload or drag and drop</div>
                                <div class="file-upload-hint">JPG, PNG, PDF (Max. 5MB)</div>
                                <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attachment Notes</label>
                            <textarea name="attachment_notes" class="form-control" rows="4" placeholder="Enter notes about attachment (optional)">{{ old('attachment_notes', $expense?->attachment_notes) }}</textarea>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                        <a href="{{ route('society.expenses.index') }}" class="btn btn-secondary">Cancel</a>
                        @unless($isEdit)
                            <button type="submit" class="btn btn-outline-primary" onclick="document.getElementById('saveAndAdd').value='1'">Save &amp; Add Another</button>
                        @endunless
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Expense' : 'Save Expense' }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Expense Summary</div>
                    <div class="review-row"><span class="review-label">Expense Title</span><span class="review-value" id="sumTitle">-</span></div>
                    <div class="review-row"><span class="review-label">Category</span><span class="review-value" id="sumCategory">-</span></div>
                    <div class="review-row"><span class="review-label">Vendor</span><span class="review-value" id="sumVendor">-</span></div>
                    <div class="review-row" style="border-top: 1px solid var(--border-color); margin-top: 6px; padding-top: 12px;"><span class="review-label">Amount</span><span class="review-value" id="sumAmount">&#8377; 0.00</span></div>
                    <div class="review-row"><span class="review-label">Tax / GST (+)</span><span class="review-value" id="sumTax">&#8377; 0.00</span></div>
                    <div class="review-row" style="border-top: 1px solid var(--border-color); margin-top: 6px; padding-top: 12px;"><span class="review-label" style="font-weight: 700; color: var(--text-primary);">Total Amount</span><span class="review-value" id="sumTotal" style="color: var(--success); font-weight: 700;">&#8377; 0.00</span></div>
                    <div class="review-row"><span class="review-label">Paid Amount</span><span class="review-value" id="sumPaid">&#8377; 0.00</span></div>
                    <div class="review-row"><span class="review-label">Due Amount</span><span class="review-value" id="sumDue" style="color: var(--danger);">&#8377; 0.00</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Payment Information</div>
                    <div class="review-row"><span class="review-label">Payment Mode</span><span class="review-value" id="sumMode">-</span></div>
                    <div class="review-row"><span class="review-label">Reference No.</span><span class="review-value" id="sumRefNo">-</span></div>
                    <div class="review-row"><span class="review-label">Payment Status</span><span class="review-value"><span class="status-badge pending" id="sumStatus">Pending</span></span></div>
                </div>
            </div>

            <div class="info-box" style="background: var(--success-light); border-color: #86efac; color: #166534;">
                <i class="fas fa-circle-info"></i>
                <span><strong>Note</strong> — Please verify all details before saving.</span>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const fmt = (n) => '&#8377; ' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const $ = (id) => document.getElementById(id);
    const selText = (sel) => (sel.value ? sel.options[sel.selectedIndex].text : '-');
    const statusClass = { paid: 'paid', pending: 'pending', overdue: 'overdue', cancelled: 'cancelled' };

    function recompute() {
        const amount = parseFloat($('fldAmount').value) || 0;
        const tax = parseFloat($('fldTax').value) || 0;
        const paid = parseFloat($('fldPaid').value) || 0;
        const due = Math.max(0, (amount + tax) - paid);

        $('sumAmount').innerHTML = fmt(amount);
        $('sumTax').innerHTML = fmt(tax);
        $('sumTotal').innerHTML = fmt(amount + tax);
        $('sumPaid').innerHTML = fmt(paid);
        $('sumDue').innerHTML = fmt(due);
        $('fldDue').value = due.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function syncText() {
        $('sumTitle').textContent = $('fldTitle').value || '-';
        $('sumCategory').textContent = selText($('fldCategory'));
        $('sumVendor').textContent = selText($('fldVendor'));
        $('sumMode').textContent = selText($('fldMode'));
        $('sumRefNo').textContent = $('fldRef').value || '-';
    }

    function syncStatus() {
        const val = $('fldStatus').value;
        const badge = $('sumStatus');
        badge.textContent = $('fldStatus').options[$('fldStatus').selectedIndex].text;
        badge.className = 'status-badge ' + (statusClass[val] || 'pending');
    }

    ['input', 'change'].forEach((evt) => {
        ['fldAmount', 'fldTax', 'fldPaid'].forEach((id) => $(id).addEventListener(evt, recompute));
        ['fldTitle', 'fldRef'].forEach((id) => $(id).addEventListener(evt, syncText));
        ['fldCategory', 'fldVendor', 'fldMode'].forEach((id) => $(id).addEventListener(evt, syncText));
    });
    $('fldStatus').addEventListener('change', syncStatus);

    $('fldDescription').addEventListener('input', function () {
        $('descCount').textContent = this.value.length;
    });

    $('attachment').addEventListener('change', function () {
        if (this.files.length) {
            this.closest('.file-upload').querySelector('.file-upload-text').textContent = this.files[0].name;
        }
    });

    recompute();
    syncText();
    syncStatus();
})();
</script>
@endpush
