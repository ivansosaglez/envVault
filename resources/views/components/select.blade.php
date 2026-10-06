<select {{ $attributes->merge(['class' => 'block w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100']) }}>
    {{ $slot }}
</select>
