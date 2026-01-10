@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md border border-gray-200' . ($padding ? ' p-6' : '')]) }}>
    {{ $slot }}
</div>
