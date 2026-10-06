{{-- Environment health: status badge, configured percentage and a progress bar. --}}
@props(['report', 'compact' => false])

@php
    $status = $report->status();
    $variant = ['healthy' => 'success', 'attention' => 'warning', 'incomplete' => 'danger'][$status->value];
    $bar = ['healthy' => 'bg-emerald-500', 'attention' => 'bg-amber-500', 'incomplete' => 'bg-red-500'][$status->value];
    $icon = ['healthy' => '✓', 'attention' => '⚠', 'incomplete' => '✗'][$status->value];
@endphp

<div {{ $attributes }}>
    <div class="flex items-center justify-between gap-3">
        <x-badge :variant="$variant">{{ $icon }} {{ $status->label() }}</x-badge>
        <span class="text-sm font-medium tabular-nums">{{ $report->percentage() }}%</span>
    </div>
    <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800" role="progressbar" aria-valuenow="{{ $report->percentage() }}" aria-valuemin="0" aria-valuemax="100">
        <div class="{{ $bar }} h-full rounded-full transition-all" style="width: {{ $report->percentage() }}%"></div>
    </div>
    @unless ($compact)
        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
            {{ $report->expected }} expected · {{ $report->configured }} configured
            @if ($report->missing) · {{ count($report->missing) }} missing @endif
            @if ($report->empty) · {{ count($report->empty) }} empty @endif
        </p>
    @endunless
</div>
