{{--
    Two ways to drive it:
      - Livewire:  <x-modal wire:model="showForm">   (open state is entangled with the component)
      - Events:    <x-modal name="x"> + $dispatch('open-modal', 'x')   (used by Breeze's profile views)
--}}
@props([
    'name' => null,
    'show' => false,
    'maxWidth' => 'lg',
])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
    ][$maxWidth];

    $model = $attributes->wire('model')->value();
@endphp

<div
    x-data="{ show: {{ $model ? "\$wire.entangle('{$model}')" : \Illuminate\Support\Js::from($show) }} }"
    x-init="$watch('show', value => document.body.classList.toggle('overflow-y-hidden', value))"
    @if ($name)
        x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
        x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    @endif
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-end justify-center overflow-y-auto px-4 py-6 sm:items-center sm:px-0"
    style="display: none;"
>
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        x-on:click="show = false"
    ></div>

    <div
        x-show="show"
        x-trap.noscroll.inert="show"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95"
        class="relative w-full {{ $maxWidth }} rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 sm:mx-auto"
        role="dialog" aria-modal="true"
    >
        {{ $slot }}
    </div>
</div>
