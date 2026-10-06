@props(['title', 'subtitle' => null])

<div {{ $attributes->class('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="min-w-0">
        @isset($breadcrumb)
            <div class="mb-1.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $breadcrumb }}</div>
        @endisset
        <h1 class="truncate text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
