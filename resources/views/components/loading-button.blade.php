@props([
    'variant' => 'primary',
    'loadingText' => 'Processing...',
])

@php
$variantClasses = match($variant) {
    'primary' => 'bg-primary-600 hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:ring-primary-500 text-white',
    'secondary' => 'bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-500 text-gray-700 shadow-sm',
    'danger' => 'bg-red-600 hover:bg-red-500 active:bg-red-700 focus:ring-red-500 text-white',
    default => 'bg-primary-600 hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:ring-primary-500 text-white',
};

$borderClass = $variant === 'secondary' ? 'border' : 'border border-transparent';
@endphp

<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => "inline-flex items-center justify-center px-4 py-2 $borderClass rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 $variantClasses"
    ]) }}
    x-data="{ loading: false }"
    x-on:click="
        if (!loading) {
            loading = true;
            $nextTick(() => {
                if ($el.form) {
                    $el.form.requestSubmit($el);
                }
            });
        }
    "
    x-bind:disabled="loading"
>
    {{-- Loading State --}}
    <span x-show="loading" class="flex items-center">
        <x-loading-spinner size="sm" class="mr-2" />
        <span>{{ $loadingText }}</span>
    </span>

    {{-- Default State --}}
    <span x-show="!loading">
        {{ $slot }}
    </span>
</button>
