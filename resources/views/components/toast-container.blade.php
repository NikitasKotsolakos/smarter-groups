{{--
Toast Notification Container
Place this component once in your layout file (app.blade.php).
It handles both server-side flash messages and client-side events.

Usage from JavaScript:
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'Success!', type: 'success' }
}));

Types: success, error, warning, info
--}}

<div
    x-data="{
        toasts: [],
        nextId: 0,

        addToast(message, type = 'info', duration = 5000) {
            const id = this.nextId++;
            this.toasts.push({ id, message, type, show: true });

            if (duration > 0) {
                setTimeout(() => this.removeToast(id), duration);
            }
        },

        removeToast(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) {
                toast.show = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },

        getStyles(type) {
            return {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'info': 'bg-blue-50 border-blue-200 text-blue-800'
            }[type] || 'bg-blue-50 border-blue-200 text-blue-800';
        },

        getIconColor(type) {
            return {
                'success': 'text-green-500',
                'error': 'text-red-500',
                'warning': 'text-yellow-500',
                'info': 'text-blue-500'
            }[type] || 'text-blue-500';
        }
    }"
    @toast.window="addToast($event.detail.message, $event.detail.type || 'info', $event.detail.duration || 5000)"
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
    role="region"
    aria-label="Notifications"
>
    {{-- Server-side flash messages --}}
    @if(session('toast'))
        <template x-init="addToast('{{ session('toast.message') }}', '{{ session('toast.type', 'info') }}')"></template>
    @endif

    {{-- Toast notifications --}}
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            :class="getStyles(toast.type)"
            class="pointer-events-auto rounded-lg border shadow-lg p-4"
            role="alert"
            aria-live="polite"
        >
            <div class="flex items-start gap-3">
                {{-- Icon --}}
                <div class="flex-shrink-0">
                    <template x-if="toast.type === 'success'">
                        <svg :class="getIconColor(toast.type)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg :class="getIconColor(toast.type)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg :class="getIconColor(toast.type)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg :class="getIconColor(toast.type)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </div>

                {{-- Message --}}
                <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

                {{-- Close Button --}}
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    class="flex-shrink-0 -mr-1 -mt-1 p-1 rounded-md hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    aria-label="Dismiss notification"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
