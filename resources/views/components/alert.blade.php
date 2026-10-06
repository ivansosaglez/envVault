@props(['variant' => 'info', 'title' => null])

@php
    $variants = [
        'info' => 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
        'danger' => 'border-red-200 bg-red-50 text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200',
    ];
@endphp

<div role="alert" {{ $attributes->class(['rounded-lg border px-4 py-3 text-sm', $variants[$variant]]) }}>
    @if ($title)
        <p class="font-medium">{{ $title }}</p>
    @endif
    <div @class(['opacity-90', 'mt-1' => $title])>{{ $slot }}</div>
</div>
