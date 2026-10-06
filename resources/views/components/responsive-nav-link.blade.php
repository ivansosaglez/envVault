@props(['active'])

<a {{ $attributes->class([
    'block rounded-md px-3 py-2 text-base font-medium transition',
    'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' => $active ?? false,
    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' => ! ($active ?? false),
]) }}>{{ $slot }}</a>
