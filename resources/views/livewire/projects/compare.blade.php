@use('App\Enums\VariableStatus')

<div class="space-y-6">
    <x-page-header title="Compare environments" subtitle="Spot what is missing, extra or different between two environments. Secrets stay hidden.">
        <x-slot name="breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">Projects</a>
            <span class="mx-1 text-zinc-300 dark:text-zinc-600">/</span>
            <a href="{{ route('projects.show', $project->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">{{ $project->name }}</a>
        </x-slot>
    </x-page-header>

    @if ($environments->count() < 2)
        <x-empty-state title="You need two environments to compare." description="Add another environment to this project first.">
            <x-button :href="route('projects.show', $project->slug)" wire:navigate>Back to project</x-button>
        </x-empty-state>
    @else
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <x-select wire:model.live="left" aria-label="Left environment" class="sm:max-w-[14rem]">
                @foreach ($environments as $environment)<option value="{{ $environment->slug }}">{{ $environment->name }}</option>@endforeach
            </x-select>
            <x-button variant="ghost" size="sm" wire:click="swap" class="self-start sm:self-auto" aria-label="Swap environments">⇄ Swap</x-button>
            <x-select wire:model.live="right" aria-label="Right environment" class="sm:max-w-[14rem]">
                @foreach ($environments as $environment)<option value="{{ $environment->slug }}">{{ $environment->name }}</option>@endforeach
            </x-select>
        </div>

        @if (! $result)
            <x-alert variant="warning">Pick two different environments to compare.</x-alert>
        @elseif ($result->total() === 0)
            <x-empty-state title="Nothing to compare." description="Neither environment has variables yet." />
        @else
            @php
                $chips = [
                    ['all', 'All', $result->total(), 'text-zinc-900 dark:text-zinc-100'],
                    [VariableStatus::Same->value, '✓ Same', $result->count(VariableStatus::Same), 'text-emerald-600 dark:text-emerald-400'],
                    [VariableStatus::Different->value, '⚠ Different', $result->count(VariableStatus::Different), 'text-amber-600 dark:text-amber-400'],
                    [VariableStatus::Missing->value, "✗ Missing in {$rightEnvironment->name}", $result->count(VariableStatus::Missing), 'text-red-600 dark:text-red-400'],
                    [VariableStatus::Extra->value, "+ Only in {$rightEnvironment->name}", $result->count(VariableStatus::Extra), 'text-sky-600 dark:text-sky-400'],
                ];
            @endphp

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                @foreach ($chips as [$value, $label, $count, $tone])
                    <button wire:click="filter('{{ $value }}')" wire:key="chip-{{ $value }}" @class([
                        'ev-card px-4 py-3 text-left transition hover:border-zinc-300 dark:hover:border-zinc-700',
                        '!border-emerald-500 ring-1 ring-emerald-500' => $status === $value,
                    ])>
                        <div class="truncate text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums {{ $tone }}">{{ $count }}</div>
                    </button>
                @endforeach
            </div>

            @if ($result->inSync())
                <x-alert variant="success" title="Fully in sync.">{{ $leftEnvironment->name }} and {{ $rightEnvironment->name }} define the same variables with the same values.</x-alert>
            @endif

            @if (count($rows) === 0)
                <x-empty-state title="No variables in this view." description="Choose another filter to see more." />
            @else
                <x-table>
                    <x-slot name="head">
                        <tr>
                            <th scope="col">Variable</th>
                            <th scope="col">{{ $leftEnvironment->name }}</th>
                            <th scope="col">{{ $rightEnvironment->name }}</th>
                            <th scope="col" class="w-36"><span class="sr-only">Status</span></th>
                        </tr>
                    </x-slot>

                    @foreach ($rows as $row)
                        <tr wire:key="row-{{ $row->key }}" @class(['bg-amber-50/40 dark:bg-amber-500/[0.04]' => $row->status === VariableStatus::Different])>
                            <td class="ev-mono font-medium">
                                {{ $row->key }}
                                @if ($row->isSecret()) <x-badge class="ml-1.5 align-middle">Secret</x-badge> @endif
                            </td>
                            <td><x-compare-cell :variable="$row->left" absent="— not set" /></td>
                            <td><x-compare-cell :variable="$row->right" absent="✗ missing" absent-tone="danger" /></td>
                            <td class="text-right">
                                @switch($row->status)
                                    @case(VariableStatus::Same) <x-badge variant="success">✓ Same</x-badge> @break
                                    @case(VariableStatus::Different) <x-badge variant="warning">⚠ Different</x-badge> @break
                                    @case(VariableStatus::Missing) <x-badge variant="danger">✗ Missing</x-badge> @break
                                    @case(VariableStatus::Extra) <x-badge variant="info">+ Extra</x-badge> @break
                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        @endif
    @endif
</div>
