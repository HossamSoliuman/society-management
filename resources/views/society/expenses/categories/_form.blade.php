@php
    /**
     * @var \App\Models\ExpenseCategory|null $category
     * @var string $action
     * @var string $mode           'create' | 'edit'
     * @var array $icons           fa-class => [label, color]
     */
    $isEdit = ($mode ?? 'create') === 'edit';
    $nameValue = old('name', $category?->name);
    $descriptionValue = old('description', $category?->description);
    $notesValue = old('notes', $category?->notes);
    $iconValue = old('icon', $category?->icon);
    $statusValue = old('status', $category?->status ?? 'active');
    $applicableValue = old('applicable_for', $category?->applicable_for ?? 'all_buildings');
    $applicables = [
        'all_buildings' => ['All Buildings', 'fa-building', 'purple', 'This category will be available for all buildings in the society.'],
        'specific_buildings' => ['Specific Buildings', 'fa-building', 'orange', 'Select specific buildings where this category is applicable.'],
        'specific_wings' => ['Specific Wings', 'fa-users', 'green', 'Select specific wings/blocks where this category is applicable.'],
    ];
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
            {{-- Card 1: Category Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--purple-light); color: var(--purple);"><i class="fas fa-file-lines"></i></div>
                        <div><div class="title" style="font-size: 16px;">Category Information</div></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category Name <span class="required">*</span></label>
                            <input type="text" name="name" id="catName" value="{{ $nameValue }}" class="form-control" placeholder="Enter category name (e.g. Maintenance)" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category Icon</label>
                            <div style="position: relative;">
                                <i class="fas fa-tag" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;"></i>
                                <select name="icon" id="catIcon" class="form-control" style="padding-left: 34px;">
                                    <option value="">Select Icon</option>
                                    @foreach($icons as $faClass => [$label, $color])
                                        <option value="{{ $faClass }}" data-icon="{{ $faClass }}" data-color="{{ $color }}" {{ $iconValue === $faClass ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="catDescription" class="form-control" rows="3" maxlength="200" placeholder="Enter description for this category (optional)">{{ $descriptionValue }}</textarea>
                        <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="descCount">{{ mb_strlen((string) $descriptionValue) }}</span> / 200</div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Category Settings --}}
            <div class="card">
                <div class="card-body">
                    <div class="card-icon-header">
                        <div class="icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-gear"></i></div>
                        <div><div class="title" style="font-size: 16px;">Category Settings</div></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" id="catStatus" class="form-control" required>
                                <option value="active" {{ $statusValue === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $statusValue === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="form-text">Inactive categories will not be visible while adding expenses.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" min="0" value="{{ old('display_order', $category?->display_order) }}" class="form-control" placeholder="Enter display order (1, 2, 3...)">
                            <div class="form-text">Lower numbers appear first in the list.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Applicable For <span class="required">*</span></label>
                        <div class="radio-card-group">
                            @foreach($applicables as $val => [$label, $icon, $color, $desc])
                                @php [$bg, $fg] = \App\Models\ExpenseCategory::tint($color); @endphp
                                <label class="radio-card" style="display: flex; align-items: flex-start; gap: 12px; padding: 16px;">
                                    <input type="radio" name="applicable_for" value="{{ $val }}" {{ $applicableValue === $val ? 'checked' : '' }} style="position: static; opacity: 1; margin-top: 3px; accent-color: var(--primary);">
                                    <span class="rli-ico" style="background: {{ $bg }}; color: {{ $fg }};"><i class="fas {{ $icon }}"></i></span>
                                    <span>
                                        <span style="font-weight: 600; display: block; margin-bottom: 2px;">{{ $label }}</span>
                                        <span style="font-size: 12px; color: var(--text-muted);">{{ $desc }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Notes <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                        <textarea name="notes" id="catNotes" class="form-control" rows="3" maxlength="200" placeholder="Enter any additional notes about this category">{{ $notesValue }}</textarea>
                        <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="notesCount">{{ mb_strlen((string) $notesValue) }}</span> / 200</div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <a href="{{ route('society.expenses.categories.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Category' : 'Save Category' }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            {{-- Preview --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Preview</div>
                    <div style="border: 2px dashed var(--border-color); border-radius: var(--radius-md); padding: 28px 20px; text-align: center;">
                        @php
                            $previewColor = $iconValue && isset($icons[$iconValue]) ? $icons[$iconValue][1] : 'purple';
                            [$pbg, $pfg] = \App\Models\ExpenseCategory::tint($previewColor);
                        @endphp
                        <div id="previewIcon" style="width: 64px; height: 64px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 14px; background: {{ $pbg }}; color: {{ $pfg }};">
                            <i class="fas {{ $iconValue ?: 'fa-screwdriver-wrench' }}"></i>
                        </div>
                        <div id="previewName" style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">{{ $nameValue ?: 'Category Name' }}</div>
                        <div id="previewDesc" style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">{{ $descriptionValue ?: 'Category Description will appear here' }}</div>
                        <span id="previewBadge" class="badge {{ $statusValue === 'active' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($statusValue) }}</span>
                    </div>
                </div>
            </div>

            {{-- Guidelines --}}
            <div class="tips-card">
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">Guidelines</div>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li><i class="fas fa-circle-check"></i><span>Category name should be clear and descriptive.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Choose an appropriate icon to easily identify the category.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Set the display order to organize categories in the desired sequence.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Inactive categories will not appear in expense entry forms.</span></li>
                </ul>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const tints = @json(collect($icons)->mapWithKeys(fn ($v, $k) => [$k => \App\Models\ExpenseCategory::tint($v[1])]));

    $('catName').addEventListener('input', function () {
        $('previewName').textContent = this.value || 'Category Name';
    });
    $('catDescription').addEventListener('input', function () {
        $('previewDesc').textContent = this.value || 'Category Description will appear here';
        $('descCount').textContent = this.value.length;
    });
    $('catNotes').addEventListener('input', function () {
        $('notesCount').textContent = this.value.length;
    });
    $('catIcon').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const icon = opt.dataset.icon || 'fa-screwdriver-wrench';
        const tint = tints[opt.value] || ['var(--purple-light)', 'var(--purple)'];
        const box = $('previewIcon');
        box.innerHTML = '<i class="fas ' + icon + '"></i>';
        box.style.background = tint[0];
        box.style.color = tint[1];
    });
    $('catStatus').addEventListener('change', function () {
        const badge = $('previewBadge');
        const active = this.value === 'active';
        badge.textContent = active ? 'Active' : 'Inactive';
        badge.className = 'badge ' + (active ? 'badge-success' : 'badge-danger');
    });
})();
</script>
@endpush
