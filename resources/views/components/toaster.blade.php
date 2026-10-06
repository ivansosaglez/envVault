{{-- Listens for `toast` events dispatched from Livewire: $this->dispatch('toast', message: '...') --}}
<div
    x-data="{ toasts: [], add(e) { const t = { id: Date.now() + Math.random(), message: e.detail.message ?? e.detail[0]?.message, variant: e.detail.variant ?? 'success' }; this.toasts.push(t); setTimeout(() => this.toasts = this.toasts.filter(x => x.id !== t.id), 3500) } }"
    x-on:toast.window="add($event)"
    class="pointer-events-none fixed inset-x-0 bottom-4 z-[60] flex flex-col items-center gap-2 px-4 sm:items-end sm:px-6"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition class="pointer-events-auto flex items-center gap-2.5 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
            <span class="size-2 rounded-full" :class="toast.variant === 'danger' ? 'bg-red-500' : 'bg-emerald-500'"></span>
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
