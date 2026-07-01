@php
    $settingsTabs = [
        ['route' => 'society.billing.settings.general', 'label' => 'General Settings'],
        ['route' => 'society.billing.settings.charge-heads', 'label' => 'Charge Heads'],
        ['route' => 'society.billing.settings.design', 'label' => 'Bill Design'],
        ['route' => 'society.billing.settings.late-fee', 'label' => 'Late Fee & Penalty'],
        ['route' => 'society.billing.settings.taxes', 'label' => 'Taxes'],
        ['route' => 'society.billing.settings.notifications', 'label' => 'Notifications'],
        ['route' => 'society.billing.settings.numbering', 'label' => 'Numbering'],
    ];
@endphp
<div class="sub-nav-tabs" style="overflow-x: auto;">
    @foreach($settingsTabs as $tab)
        <a href="{{ route($tab['route']) }}" class="sub-nav-tab {{ request()->routeIs($tab['route']) ? 'active' : '' }}">{{ $tab['label'] }}</a>
    @endforeach
</div>
