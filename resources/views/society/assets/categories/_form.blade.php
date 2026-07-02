@php
    /**
     * @var \App\Models\AssetCategory|null $category
     * @var string $action
     * @var string $mode
     * @var array $icons          fa-class => [label, color]
     * @var \Illuminate\Support\Collection $parents
     */
    $isEdit = ($mode ?? 'create') === 'edit';
    $nameValue = old('name', $category?->name);
    $codeValue = old('code', $category?->code);
    $descriptionValue = old('description', $category?->description);
    $notesValue = old('notes', $category?->notes);
    $iconValue = old('icon', $category?->icon);
    $statusValue = old('status', $category?->status ?? 'active');
    $movableValue = old('movable', $category?->movable ?? true);
    $immovableValue = old('immovable', $category?->immovable ?? true);
    $previewColor = $iconValue && isset($icons[$iconValue]) ? $icons[$iconValue][1] : 'purple';
    [$pbg, $pfg] = \App\Models\AssetCategory::tint($previewColor);
@endphp

<form method="POST" action="{{ $action }}" id="assetCategoryForm">
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
            {{-- 1. Category Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">1. Category Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category Name <span class="required">*</span></label>
                            <input type="text" name="name" id="catName" value="{{ $nameValue }}" class="form-control" placeholder="Enter category name (e.g. Lift)" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category Code <span class="required">*</span></label>
                            <input type="text" name="code" value="{{ $codeValue }}" class="form-control" placeholder="e.g. LIFT" required>
                            <div class="form-text">Short code for internal reference (e.g. LIFT, GEN, SEC).</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="catDescription" class="form-control" rows="3" maxlength="255" placeholder="Enter description for this category (optional)">{{ $descriptionValue }}</textarea>
                            <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="descCount">{{ mb_strlen((string) $descriptionValue) }}</span> / 255</div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Icon <span class="required">*</span></label>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div id="iconBox" style="width: 48px; height: 48px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; background: {{ $pbg }}; color: {{ $pfg }};"><i class="fas {{ $iconValue ?: 'fa-image' }}"></i></div>
                                <div style="flex: 1; position: relative;">
                                    <select name="icon" id="catIcon" class="form-control">
                                        <option value="">Choose Icon</option>
                                        @foreach($icons as $faClass => [$label, $color])
                                            <option value="{{ $faClass }}" data-color="{{ $color }}" {{ $iconValue === $faClass ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-text">Choose an icon to represent this category.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Category Settings --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">2. Category Settings</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" id="catStatus" class="form-control" required>
                                <option value="active" {{ $statusValue === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $statusValue === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="form-text">Inactive categories will not be available for new assets.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" min="0" value="{{ old('display_order', $category?->display_order ?? 0) }}" class="form-control" placeholder="0">
                            <div class="form-text">Set display order in the list (lower number shows first).</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Applicable For</label>
                        <div style="display: flex; gap: 24px; padding: 4px 0;">
                            <label class="form-check" style="margin: 0;">
                                <input type="checkbox" name="movable" value="1" {{ $movableValue ? 'checked' : '' }} style="accent-color: var(--primary);">
                                <span>Movable Assets</span>
                            </label>
                            <label class="form-check" style="margin: 0;">
                                <input type="checkbox" name="immovable" value="1" {{ $immovableValue ? 'checked' : '' }} style="accent-color: var(--primary);">
                                <span>Immovable Assets</span>
                            </label>
                        </div>
                        <div class="form-text">Select the type of assets this category applies to.</div>
                    </div>
                </div>
            </div>

            {{-- 3. Additional Information --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">3. Additional Information <span style="color: var(--text-muted); font-weight: 400; font-size: 13px;">(Optional)</span></div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-control">
                                <option value="">Select parent category (if any)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ (string) old('parent_id', $category?->parent_id) === (string) $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose a parent category to create a sub-category.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Asset Life (Years)</label>
                            <input type="number" min="0" name="asset_life_years" value="{{ old('asset_life_years', $category?->asset_life_years) }}" class="form-control" placeholder="Enter expected life in years">
                            <div class="form-text">Average expected life of assets in this category.</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="catNotes" class="form-control" rows="3" maxlength="255" placeholder="Enter any additional notes">{{ $notesValue }}</textarea>
                        <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="notesCount">{{ mb_strlen((string) $notesValue) }}</span> / 255</div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <a href="{{ route('society.assets.categories.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> {{ $isEdit ? 'Update Category' : 'Save Category' }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            {{-- Icon Preview --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Icon Preview</div>
                    <div style="background: var(--purple-light); border-radius: var(--radius-md); padding: 32px 20px; text-align: center;">
                        <div id="previewIcon" style="width: 72px; height: 72px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 14px; background: {{ $pbg }}; color: {{ $pfg }};">
                            <i class="fas {{ $iconValue ?: 'fa-cube' }}"></i>
                        </div>
                        <div id="previewName" style="font-size: 15px; font-weight: 700; color: var(--text-primary);">{{ $nameValue ?: 'Category Icon' }}</div>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="tips-card">
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">Tips</div>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li><i class="fas fa-circle-check"></i><span>Choose a clear and relevant name for the category.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Use a unique code for quick internal reference.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Set the display order to organize the list.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>You can update details anytime later.</span></li>
                </ul>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const tints = @json(collect($icons)->mapWithKeys(fn ($v, $k) => [$k => \App\Models\AssetCategory::tint($v[1])]));

    $('catName').addEventListener('input', function () {
        $('previewName').textContent = this.value || 'Category Icon';
    });
    $('catDescription').addEventListener('input', function () {
        $('descCount').textContent = this.value.length;
    });
    $('catNotes').addEventListener('input', function () {
        $('notesCount').textContent = this.value.length;
    });
    $('catIcon').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const icon = opt.value || 'fa-cube';
        const tint = tints[opt.value] || ['var(--purple-light)', 'var(--purple)'];
        [$('previewIcon'), $('iconBox')].forEach((box) => {
            box.innerHTML = '<i class="fas ' + icon + '"></i>';
            box.style.background = tint[0];
            box.style.color = tint[1];
        });
    });
})();
</script>
@endpush
