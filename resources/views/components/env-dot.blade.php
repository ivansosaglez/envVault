@props(['color' => null])

@php
    $colors = [
        'emerald' => 'bg-emerald-500',
        'sky' => 'bg-sky-500',
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        'violet' => 'bg-violet-500',
        'zinc' => 'bg-zinc-400',
    ];
@endphp

<span {{ $attributes->class(['inline-block size-2 shrink-0 rounded-full', $colors[$color] ?? 'bg-zinc-400']) }}></span>
