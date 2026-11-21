@props([
    'url',
    'target',
    'selected' => 'this_month',
])

{{--
    Card-level period selector. Fetches {url}?range=… and swaps the element
    with id "card-{key}" for every fragment key the endpoint returns.
--}}
<select data-period-select data-url="{{ $url }}" data-fragment-prefix="card-" data-scope="#card-{{ $target }}"
        {{ $attributes->class(['form-control', 'period-select']) }} aria-label="Period">
    @foreach(\App\Support\Period::options() as $key => $label)
        <option value="{{ $key }}" @selected($key === $selected)>{{ $label }}</option>
    @endforeach
</select>
