{{--
    Checkbox-list picker used by the late-fee exemptions card.

    @param string $name      input name (without [])
    @param string $label
    @param string $icon      Font Awesome icon class
    @param string $help
    @param array<int, array{value: string|int, label: string, meta?: string|null}> $options
    @param array<int, string|int> $selected
    @param bool   $searchable
    @param string $empty     message shown when there are no options
--}}
@php
    $selectedValues = array_map('strval', $selected);
    $selectedCount = count(array_intersect($selectedValues, array_map(fn ($o) => (string) $o['value'], $options)));
@endphp
<div class="exempt-picker" data-exempt-picker>
    <div class="exempt-picker-head">
        <div class="exempt-picker-title"><i class="fas {{ $icon }}"></i> {{ $label }}</div>
        <span class="exempt-picker-count {{ $selectedCount ? 'active' : '' }}" data-count>{{ $selectedCount }} selected</span>
    </div>

    @if(count($options))
        @if($searchable ?? false)
            <div class="exempt-picker-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" class="form-control" placeholder="Search..." data-search aria-label="Search {{ strtolower($label) }}">
            </div>
        @endif

        <div class="exempt-picker-list">
            @foreach($options as $option)
                <label class="exempt-picker-item" data-label="{{ strtolower($option['label'].' '.($option['meta'] ?? '')) }}">
                    <input type="checkbox" class="form-check-input" name="{{ $name }}[]" value="{{ $option['value'] }}" {{ in_array((string) $option['value'], $selectedValues, true) ? 'checked' : '' }}>
                    <span class="exempt-picker-label">{{ $option['label'] }}</span>
                    @if(! empty($option['meta']))
                        <span class="exempt-picker-meta">{{ $option['meta'] }}</span>
                    @endif
                </label>
            @endforeach
            <div class="exempt-picker-empty" data-no-results hidden>No matches found</div>
        </div>

        <div class="exempt-picker-actions">
            <button type="button" class="link-btn" data-select-all>Select all</button>
            <span>&middot;</span>
            <button type="button" class="link-btn" data-clear>Clear</button>
        </div>
    @else
        <div class="exempt-picker-empty">{{ $empty }}</div>
    @endif

    <div class="form-text">{{ $help }}</div>
</div>
