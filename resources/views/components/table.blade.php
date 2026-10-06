{{-- Responsive data table inside a card. Slots: head (<tr> with <th>) and the default slot (<tr> rows). --}}
<div {{ $attributes->class('ev-card overflow-hidden') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
            <thead class="bg-zinc-50/70 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400 [&_th]:px-3 sm:[&_th]:px-4 [&_th]:py-3 [&_th]:font-medium">
                {{ $head }}
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/70 [&_td]:px-3 sm:[&_td]:px-4 [&_td]:py-3">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
