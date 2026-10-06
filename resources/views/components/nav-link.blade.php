@props(['active'])

<a {{ $attributes->class([
    'inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium transition',
    'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' => $active ?? false,
    'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' => ! ($active ?? false),
]) }}>{{ $slot }}</a>
