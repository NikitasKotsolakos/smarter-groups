@props(['bordered' => true])

@php
$classes = 'min-w-full divide-y divide-gray-200';
if ($bordered) {
    $classes .= ' border border-gray-200';
}
@endphp

<div class="overflow-x-auto rounded-lg shadow">
    <table {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </table>
</div>
