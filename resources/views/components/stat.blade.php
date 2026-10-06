@props(['label', 'value', 'tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'text-zinc-900 dark:text-zinc-100',
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        'danger' => 'text-red-600 dark:text-red-400',
        'info' => 'text-sky-600 dark:text-sky-400',
    ];
@endphp

<div {{ $attributes->class('ev-card px-4 py-3.5') }}>
    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
    <div class="mt-1 text-2xl font-semibold tabular-nums {{ $tones[$tone] }}">{{ $value }}</div>
</div>
