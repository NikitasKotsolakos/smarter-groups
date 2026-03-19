@props(['showText' => true])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" aria-hidden="true">
        <defs>
            <linearGradient id="logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#4f46e5" />
                <stop offset="100%" style="stop-color:#9333ea" />
            </linearGradient>
        </defs>
        <rect width="40" height="40" rx="8" fill="url(#logo-gradient)" />
        <g fill="white">
            <circle cx="14" cy="14" r="4" />
            <circle cx="26" cy="14" r="4" />
            <circle cx="14" cy="26" r="4" />
            <circle cx="26" cy="26" r="4" />
        </g>
    </svg>
    @if($showText)
        <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Group Splitter
        </span>
    @endif
</div>
