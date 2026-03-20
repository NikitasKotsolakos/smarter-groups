@props([
    'checked' => false,
])

<input
    type="checkbox"
    {{ $checked ? 'checked' : '' }}
    {!! $attributes->merge(['class' => 'rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-0']) !!}
>
