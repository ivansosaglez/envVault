<div class="space-y-6">
    <x-page-header :title="$environment->name">
        <x-slot name="breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">Projects</a>
            <span class="mx-1 text-zinc-300 dark:text-zinc-600">/</span>
            <a href="{{ route('projects.show', $project->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">{{ $project->name }}</a>
        </x-slot>
        <x-slot name="actions">
            <x-button variant="secondary" size="sm" wire:click="openImport">Import .env</x-button>
            <x-button variant="secondary" size="sm" :href="route('projects.example', [$project->slug, 'environment' => $environment->slug])" wire:navigate>.env.example</x-button>
            <x-button variant="secondary" size="sm" :href="route('projects.validate', [$project->slug, 'environment' => $environment->slug])" wire:navigate>Validate</x-button>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <x-button variant="ghost" size="sm" aria-label="Environment settings">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" /></svg>
                    </x-button>
                </x-slot>
                <x-slot name="content">
                    <button class="w-full" wire:click="$set('confirmingEnvironmentDelete', true)"><x-dropdown-link class="!text-red-600 dark:!text-red-400">Delete environment</x-dropdown-link></button>
                </x-slot>
            </x-dropdown>
        </x-slot>
    </x-page-header>

    {{-- Sibling environments --}}
    <nav class="-mt-2 flex flex-wrap gap-1.5" aria-label="Environments">
        <span class="ev-card inline-flex items-center gap-2 border-zinc-300 px-3 py-1.5 text-sm font-medium dark:border-zinc-600"><x-env-dot :color="$environment->color" /> {{ $environment->name }}</span>
        @foreach ($otherEnvironments as $other)
            <a href="{{ route('projects.environments.show', [$project->slug, $other->slug]) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800">
                <x-env-dot :color="$other->color" /> {{ $other->name }}
            </a>
        @endforeach
    </nav>

    @if ($totalVariables > 0)
        <x-card class="!p-4">
            <div class="grid items-center gap-4 sm:grid-cols-3">
                <div>
                    <div class="text-2xl font-semibold tabular-nums">{{ $totalVariables }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ Str::plural('variable', $totalVariables) }}</div>
                </div>
                <x-health :report="$report" class="sm:col-span-2" />
            </div>
            @if ($report->missing)
                <p class="mt-3 border-t border-zinc-100 pt-3 text-xs text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    <span class="font-medium text-red-600 dark:text-red-400">Missing here:</span>
                    <span class="ev-mono">{{ implode(', ', array_slice($report->missing, 0, 8)) }}{{ count($report->missing) > 8 ? ', …' : '' }}</span>
                </p>
            @endif
        </x-card>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
            <x-text-input type="search" wire:model.live.debounce.250ms="search" placeholder="Search variables…" class="!pl-9" aria-label="Search variables" />
        </div>
        <x-button wire:click="openCreate">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add variable
        </x-button>
    </div>

    @if ($variables->isEmpty())
        @if ($search !== '')
            <x-empty-state title="No variables match “{{ $search }}”." description="Try a different search term." />
        @else
            <x-empty-state title="No variables found." description="Add your first environment variable, or import an existing .env file.">
                <div class="flex gap-2">
                    <x-button wire:click="openCreate">Add variable</x-button>
                    <x-button variant="secondary" wire:click="openImport">Import .env</x-button>
                </div>
            </x-empty-state>
        @endif
    @else
        <x-table>
            <x-slot name="head">
                <tr>
                    <th scope="col">
                        <button wire:click="toggleSort" class="inline-flex items-center gap-1 uppercase tracking-wide hover:text-zinc-900 dark:hover:text-zinc-100" aria-label="Sort by key">
                            Key <span aria-hidden="true">{{ $sort === 'asc' ? '↑' : '↓' }}</span>
                        </button>
                    </th>
                    <th scope="col">Value</th>
                    <th scope="col" class="hidden w-24 sm:table-cell">Secret</th>
                    <th scope="col" class="w-20 sm:w-28"><span class="sr-only">Actions</span></th>
                </tr>
            </x-slot>

            @foreach ($variables as $variable)
                @php($isRevealed = $variable->is_secret && $revealedValues->has($variable->id))
                <tr wire:key="variable-{{ $variable->id }}" class="group">
                    <td class="ev-mono max-w-[8rem] truncate font-medium sm:max-w-[16rem]" title="{{ $variable->key }}">{{ $variable->key }}</td>
                    <td class="max-w-[9rem] sm:max-w-md">
                        @if ($variable->is_secret && ! $isRevealed)
                            <div class="flex items-center gap-2">
                                <span class="ev-mono max-w-[4.5rem] select-none truncate tracking-wider text-zinc-400 sm:max-w-none">{{ $variable->displayValue() }}</span>
                                @if ($variable->hasValue())
                                    <button wire:click="reveal({{ $variable->id }})" class="text-xs font-medium text-emerald-600 hover:underline dark:text-emerald-400">Show</button>
                                @else
                                    <span class="text-xs italic text-zinc-400">empty</span>
                                @endif
                            </div>
                        @elseif ($isRevealed)
                            <div class="flex items-center gap-2" x-data="clipboard(@js($revealedValues[$variable->id]))" x-init="setTimeout(() => $wire.hide({{ $variable->id }}), 15000)">
                                <code class="ev-mono truncate rounded bg-amber-50 px-1.5 py-0.5 text-amber-900 dark:bg-amber-500/10 dark:text-amber-200">{{ $revealedValues[$variable->id] }}</code>
                                <button x-on:click="copy()" class="text-xs font-medium text-emerald-600 hover:underline dark:text-emerald-400" x-text="copied ? 'Copied' : 'Copy'">Copy</button>
                                <button wire:click="hide({{ $variable->id }})" class="text-xs font-medium text-zinc-500 hover:underline">Hide</button>
                            </div>
                        @elseif ($variable->hasValue())
                            <div class="flex items-center gap-2" x-data="clipboard(@js($variable->value))">
                                <span class="ev-mono truncate" title="{{ $variable->value }}">{{ $variable->value }}</span>
                                <button x-on:click="copy()" class="invisible text-xs font-medium text-emerald-600 hover:underline group-hover:visible focus:visible dark:text-emerald-400" x-text="copied ? 'Copied' : 'Copy'">Copy</button>
                            </div>
                        @else
                            <span class="text-xs italic text-zinc-400">empty</span>
                        @endif
                    </td>
                    <td class="hidden sm:table-cell">
                        @if ($variable->is_secret)
                            <x-badge variant="warning">Secret</x-badge>
                        @else
                            <span class="text-xs text-zinc-400">No</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex justify-end gap-1">
                            <x-button variant="ghost" size="sm" wire:click="openEdit({{ $variable->id }})" aria-label="Edit {{ $variable->key }}">
                                <svg class="size-4 sm:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                                <span class="hidden sm:inline">Edit</span>
                            </x-button>
                            <x-button variant="ghost" size="sm" class="hover:!text-red-600" wire:click="confirmDelete({{ $variable->id }})" aria-label="Delete {{ $variable->key }}">
                                <svg class="size-4 sm:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                <span class="hidden sm:inline">Delete</span>
                            </x-button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div>{{ $variables->links() }}</div>
    @endif

    {{-- Add / edit variable --}}
    <x-modal wire:model="showForm">
        <form wire:submit="saveVariable" class="space-y-5">
            <h2 class="text-lg font-semibold">{{ $editingId ? 'Edit variable' : 'Add variable' }}</h2>

            <x-field label="Name" name="key">
                <x-text-input wire:model="key" id="key" class="ev-mono" placeholder="DB_HOST" autocomplete="off" spellcheck="false" />
            </x-field>

            <x-field label="Value" name="value" :hint="$editingId && $isSecret && ! $valueTouched ? 'Leave blank to keep the current secret value.' : null">
                <x-textarea wire:model="value" id="value" rows="3" class="ev-mono" autocomplete="off" spellcheck="false"
                    placeholder="{{ $editingId && $isSecret ? '•••••••• (unchanged)' : 'localhost' }}" />
            </x-field>

            <label class="flex items-start gap-3">
                <input type="checkbox" wire:model="isSecret" class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-900">
                <span class="text-sm">
                    <span class="font-medium">Secret</span>
                    <span class="block text-zinc-500 dark:text-zinc-400">Hidden in lists and comparisons until you choose to show it.</span>
                </span>
            </label>

            <div class="flex justify-end gap-2">
                <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
                <x-button type="submit" wire:loading.attr="disabled">{{ $editingId ? 'Save changes' : 'Add variable' }}</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Import --}}
    <x-modal wire:model="showImport" max-width="xl">
        <div class="space-y-5">
            <div>
                <h2 class="text-lg font-semibold">Import .env</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Paste the contents of a .env file. Nothing is saved until you confirm.</p>
            </div>

            @if (! $plan)
                <form wire:submit="analyzeImport" class="space-y-4">
                    <x-field label="Contents" name="importContent">
                        <x-textarea wire:model="importContent" id="importContent" rows="10" class="ev-mono" spellcheck="false" placeholder="APP_NAME=&quot;My App&quot;&#10;APP_ENV=local&#10;DB_HOST=localhost" />
                    </x-field>
                    <div class="flex justify-end gap-2">
                        <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
                        <x-button type="submit">Review</x-button>
                    </div>
                </form>
            @elseif ($plan->total() === 0)
                <x-alert variant="warning" title="No variables detected.">Check the syntax and try again.</x-alert>
                <div class="flex justify-end"><x-button variant="secondary" wire:click="editImport">Back</x-button></div>
            @else
                <div class="space-y-4">
                    <p class="text-sm font-medium">{{ $plan->total() }} {{ Str::plural('variable', $plan->total()) }} detected</p>

                    @if ($plan->parsed->invalidLines || $plan->parsed->duplicates)
                        <x-alert variant="warning">
                            @if ($plan->parsed->invalidLines) Ignored unparseable lines: {{ implode(', ', $plan->parsed->invalidLines) }}. @endif
                            @if ($plan->parsed->duplicates) Duplicated keys use their last value: <span class="ev-mono">{{ implode(', ', $plan->parsed->duplicates) }}</span>. @endif
                        </x-alert>
                    @endif

                    @if ($plan->new)
                        <div>
                            <h3 class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">New · {{ count($plan->new) }}</h3>
                            <ul class="ev-mono max-h-32 space-y-0.5 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-800">
                                @foreach ($plan->new as $key)<li>{{ $key }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($plan->conflicts)
                        <div>
                            <h3 class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Already exist · {{ count($plan->conflicts) }}</h3>
                            <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">Existing variables are skipped unless you choose to replace them.</p>
                            <ul class="max-h-48 divide-y divide-zinc-100 overflow-y-auto rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                                @foreach ($plan->conflicts as $key)
                                    <li class="flex items-center justify-between gap-3 px-3 py-2 text-sm" wire:key="conflict-{{ $key }}">
                                        <span class="flex items-center gap-2"><span class="ev-mono">{{ $key }}</span> @if (in_array($key, $plan->secretConflicts, true)) <x-badge variant="warning">Secret</x-badge> @endif</span>
                                        <label class="flex items-center gap-2 text-xs"><input type="checkbox" wire:model="replace" value="{{ $key }}" class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-900"> Replace</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex justify-between gap-2">
                        <x-button variant="ghost" wire:click="editImport">Back</x-button>
                        <div class="flex gap-2">
                            <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
                            <x-button wire:click="confirmImport" wire:loading.attr="disabled">Import variables</x-button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-modal>

    <x-confirm-dialog wire:model="confirmingEnvironmentDelete" title="Delete {{ $environment->name }}?" confirm="deleteEnvironment" button="Delete environment"
        message="Its {{ $totalVariables }} {{ Str::plural('variable', $totalVariables) }} will be permanently deleted. This cannot be undone." />

    <x-confirm-dialog wire:model="confirmingDelete" title="Delete {{ $pendingDelete?->key }}?" confirm="deleteVariable" button="Delete variable"
        message="This variable will be removed from {{ $environment->name }}. This cannot be undone." />
</div>
