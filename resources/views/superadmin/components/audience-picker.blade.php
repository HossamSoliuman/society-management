@php
    /**
     * Society + role targeting shared by announcements and notices.
     *
     * @var \Illuminate\Support\Collection $societies
     * @var \Illuminate\Support\Collection $roles
     * @var string|null $estimateUrl   AJAX endpoint returning {count}
     * @var bool $showRoles            render role checkboxes (default true)
     */
    $showRoles = $showRoles ?? true;
    $selectedRoles = (array) old('target_roles', []);
@endphp
<div class="form-group">
    <label class="form-label">Society</label>
    <select name="society_id" class="form-control" data-audience-society>
        <option value="">All societies</option>
        @foreach($societies as $society)
            <option value="{{ $society->id }}" {{ (string) old('society_id') === (string) $society->id ? 'selected' : '' }}>{{ $society->name }}</option>
        @endforeach
    </select>
</div>
@if($showRoles)
    <div class="form-group">
        <label class="form-label">Roles <span style="font-weight: 400; color: var(--text-muted);">(none = everyone)</span></label>
        @foreach($roles as $role)
            <label style="display: flex; align-items: center; gap: 8px; padding: 4px 0; cursor: pointer;">
                <input type="checkbox" name="target_roles[]" value="{{ $role->name }}" data-audience-role @checked(in_array($role->name, $selectedRoles, true))>
                <span>{{ $role->display_name }}</span>
            </label>
        @endforeach
    </div>
@endif
<div class="info-box">
    <i class="fas fa-info-circle"></i>
    <span>Estimated recipients: <strong data-audience-count>—</strong></span>
</div>
@if(! empty($estimateUrl))
@push('scripts')
<script>
(function () {
    var society = document.querySelector('[data-audience-society]');
    var roles = document.querySelectorAll('[data-audience-role]');
    var recipientType = document.querySelector('[name="recipient_type"]');
    var out = document.querySelector('[data-audience-count]');
    if (!society || !out) return;

    function refresh() {
        var params = new URLSearchParams();
        if (society.value) params.append('society_id', society.value);
        roles.forEach(function (cb) { if (cb.checked) params.append('target_roles[]', cb.value); });
        if (recipientType) params.append('recipient_type', recipientType.value);
        fetch('{{ $estimateUrl }}?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) { out.textContent = d.count; })
            .catch(function () { out.textContent = '—'; });
    }
    society.addEventListener('change', refresh);
    roles.forEach(function (cb) { cb.addEventListener('change', refresh); });
    if (recipientType) recipientType.addEventListener('change', refresh);
    refresh();
})();
</script>
@endpush
@endif
