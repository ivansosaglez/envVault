@props(['label', 'name', 'hint' => null])

<div {{ $attributes->class('space-y-1.5') }}>
    <x-input-label :for="$name" :value="$label" />
    {{ $slot }}
    @if ($hint)
        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</p>
    @endif
    <x-input-error :messages="$errors->get($name)" />
</div>
