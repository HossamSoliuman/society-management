@props(['route', 'params' => []])

<a href="{{ route($route, array_merge(request()->except(['page', 'per_page']), $params)) }}" {{ $attributes->merge(['class' => 'btn btn-secondary']) }} style="color: var(--success); border-color: var(--success);"><i class="fas fa-file-excel"></i> Export</a>
