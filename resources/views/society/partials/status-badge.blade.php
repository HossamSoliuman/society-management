@php
    /**
     * @var string $class  the .status-badge state class (active|pending|overdue|expiring_soon|purple|cancelled)
     * @var string $label  display text
     */
@endphp
@if(($class ?? '') === 'purple')
    <span class="badge badge-purple">{{ $label }}</span>
@else
    <span class="status-badge {{ $class }}">{{ $label }}</span>
@endif
