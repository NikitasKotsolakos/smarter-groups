@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
$styles = match($type) {
    'success' => 'bg-green-50 border-green-200 text-green-700',
    'error' => 'bg-red-50 border-red-200 text-red-700',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
    'info' => 'bg-blue-50 border-blue-200 text-blue-700',
    default => 'bg-blue-50 border-blue-200 text-blue-700',
};

$iconColor = match($type) {
    'success' => 'text-green-500',
    'error' => 'text-red-500',
    'warning' => 'text-yellow-500',
    'info' => 'text-blue-500',
    default => 'text-blue-500',
};
@endphp

<div
    {{ $attributes->merge(['class' => "px-4 py-3 rounded-md border $styles"]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @endif
    role="alert"
>
    <div class="flex items-start gap-3">
        {{-- Icon --}}
        <div class="flex-shrink-0">
            @if($type === 'success')
                <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif($type === 'error')
                <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif($type === 'warning')
                <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @else
                <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </div>

        {{-- Content --}}
        <div class="flex-1 text-sm">
            {{ $slot }}
        </div>

        {{-- Dismiss Button --}}
        @if($dismissible)
            <button
                type="button"
                @click="show = false"
                class="flex-shrink-0 -mr-1 -mt-1 p-1 rounded-md hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                aria-label="Dismiss"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
