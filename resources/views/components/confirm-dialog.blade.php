{{-- Destructive confirmation. <x-confirm-dialog wire:model="confirming" title="..." confirm="delete" button="Delete" /> --}}
@props(['title', 'message' => null, 'confirm', 'button' => 'Delete'])

<x-modal {{ $attributes->whereStartsWith('wire:model') }} max-width="md">
    <div class="flex gap-4">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        </div>
        <div class="min-w-0">
            <h2 class="text-base font-semibold">{{ $title }}</h2>
            @if ($message)
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $message }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
    <div class="mt-6 flex justify-end gap-2">
        <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
        <x-button variant="danger" wire:click="{{ $confirm }}" wire:loading.attr="disabled">{{ $button }}</x-button>
    </div>
</x-modal>
