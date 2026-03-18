@props(['hover' => true])

@php
$classes = $hover ? 'hover:bg-gray-50 transition-colors' : '';
@endphp

<tr {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</tr>
