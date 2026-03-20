@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
$variantStyles = match($variant) {
    'success' => 'bg-green-100 text-green-700',
    'error' => 'bg-red-100 text-red-700',
    'warning' => 'bg-yellow-100 text-yellow-700',
    'info' => 'bg-blue-100 text-blue-700',
    'primary' => 'bg-primary-100 text-primary-700',
    'default' => 'bg-gray-100 text-gray-700',
    default => 'bg-gray-100 text-gray-700',
};

$sizeStyles = match($size) {
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-sm',
    'lg' => 'px-3 py-1 text-sm',
    default => 'px-2.5 py-0.5 text-sm',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-medium $variantStyles $sizeStyles"]) }}>
    {{ $slot }}
</span>
