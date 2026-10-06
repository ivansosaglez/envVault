<div class="space-y-8">
    <x-page-header :title="$project->name" :subtitle="$project->description">
        <x-slot name="breadcrumb"><a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">Projects</a></x-slot>
        <x-slot name="actions">
            <x-button variant="secondary" size="sm" :href="route('projects.compare', $project->slug)" wire:navigate>Compare</x-button>
            <x-button variant="secondary" size="sm" :href="route('projects.validate', $project->slug)" wire:navigate>Validate .env</x-button>
            <x-button variant="secondary" size="sm" :href="route('projects.example', $project->slug)" wire:navigate>.env.example</x-button>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <x-button variant="ghost" size="sm" aria-label="Project settings">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" /></svg>
                    </x-button>
                </x-slot>
                <x-slot name="content">
                    <button class="w-full" wire:click="$set('showEditForm', true)"><x-dropdown-link>Edit project</x-dropdown-link></button>
                    <button class="w-full" wire:click="$set('confirmingDelete', true)"><x-dropdown-link class="!text-red-600 dark:!text-red-400">Delete project</x-dropdown-link></button>
                </x-slot>
            </x-dropdown>
        </x-slot>
    </x-page-header>

    {{-- Environment tabs --}}
    <div class="-mb-2 flex flex-wrap items-center gap-2">
        @foreach ($environments as $environment)
            <a href="{{ route('projects.environments.show', [$project->slug, $environment->slug]) }}" wire:navigate wire:key="tab-{{ $environment->id }}"
               class="ev-card inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium transition hover:border-zinc-300 dark:hover:border-zinc-700">
                <x-env-dot :color="$environment->color" />
                {{ $environment->name }}
                <span class="text-xs font-normal text-zinc-400">{{ $environment->variables->count() }}</span>
            </a>
        @endforeach
        <x-button variant="ghost" size="sm" wire:click="$set('showEnvironmentForm', true)">+ Add environment</x-button>
    </div>

    @if ($environments->isEmpty())
        <x-empty-state title="No environments yet." description="Add Local, Staging or Production to start tracking variables.">
            <x-button wire:click="$set('showEnvironmentForm', true)">Add environment</x-button>
        </x-empty-state>
    @else
        {{-- Summary --}}
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-stat label="Variables" :value="$summary['total']" />
            <x-stat label="Synchronized" :value="$summary['synchronized']" tone="success" />
            <x-stat label="Missing" :value="$summary['missing']" :tone="$summary['missing'] ? 'danger' : 'neutral'" />
            <x-stat label="Different" :value="$summary['different']" :tone="$summary['different'] ? 'warning' : 'neutral'" />
        </section>

        <div class="grid gap-8 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                {{-- Health --}}
                <section>
                    <h2 class="mb-3 text-sm font-semibold">Configuration health</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($environments as $environment)
                            <x-card wire:key="health-{{ $environment->id }}" class="!p-4">
                                <div class="mb-3 flex items-center gap-2 text-sm font-medium"><x-env-dot :color="$environment->color" /> {{ $environment->name }}</div>
                                <x-health :report="$reports[$environment->id]" />
                            </x-card>
                        @endforeach
                    </div>
                </section>

                {{-- Comparison --}}
                @if ($environments->count() > 1)
                    <section>
                        <h2 class="mb-3 text-sm font-semibold">Environment comparison</h2>
                        <x-card class="!p-4">
                            <form wire:submit="compare" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <x-select wire:model="compareLeft" aria-label="Left environment">
                                    @foreach ($environments as $environment)<option value="{{ $environment->slug }}">{{ $environment->name }}</option>@endforeach
                                </x-select>
                                <span class="text-sm text-zinc-400">vs</span>
                                <x-select wire:model="compareRight" aria-label="Right environment">
                                    @foreach ($environments as $environment)<option value="{{ $environment->slug }}">{{ $environment->name }}</option>@endforeach
                                </x-select>
                                <x-button type="submit" class="sm:shrink-0">Compare</x-button>
                            </form>
                            <x-input-error :messages="$errors->get('compareLeft')" class="mt-2" />
                        </x-card>
                    </section>
                @endif
            </div>

            {{-- Activity --}}
            <section>
                <h2 class="mb-3 text-sm font-semibold">Recent changes</h2>
                <x-card class="!p-4">
                    @forelse ($activities as $activity)
                        <div class="flex items-start gap-3 py-2 text-sm" wire:key="activity-{{ $activity->id }}">
                            <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                            <div class="min-w-0">
                                <p class="break-words"><span class="font-medium">{{ $activity->user_id === auth()->id() ? 'You' : ($activity->user?->name ?? 'Someone') }}</span> {{ $activity->description() }}</p>
                                <p class="text-xs text-zinc-400" title="{{ $activity->created_at }}">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-zinc-500">No activity yet.</p>
                    @endforelse
                </x-card>
            </section>
        </div>
    @endif

    {{-- Add environment --}}
    <x-modal wire:model="showEnvironmentForm">
        <form wire:submit="addEnvironment" class="space-y-5">
            <div>
                <h2 class="text-lg font-semibold">Add environment</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">For example Local, Staging or Production.</p>
            </div>

            <x-field label="Name" name="environmentName">
                <x-text-input wire:model="environmentName" id="environmentName" placeholder="Production" />
            </x-field>

            <div class="space-y-1.5">
                <x-input-label value="Color" />
                <div class="flex gap-2">
                    @foreach (\App\Models\Environment::COLORS as $color)
                        <label class="cursor-pointer rounded-full p-1 ring-2 ring-transparent has-[:checked]:ring-zinc-400 dark:has-[:checked]:ring-zinc-500">
                            <input type="radio" wire:model="environmentColor" value="{{ $color }}" class="sr-only" aria-label="{{ $color }}">
                            <x-env-dot :color="$color" class="!size-5" />
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
                <x-button type="submit" wire:loading.attr="disabled">Add environment</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit project --}}
    <x-modal wire:model="showEditForm">
        <form wire:submit="updateProject" class="space-y-5">
            <h2 class="text-lg font-semibold">Edit project</h2>
            <x-field label="Name" name="name"><x-text-input wire:model="name" id="name" /></x-field>
            <x-field label="Description" name="description"><x-text-input wire:model="description" id="description" /></x-field>
            <div class="flex justify-end gap-2">
                <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
                <x-button type="submit">Save changes</x-button>
            </div>
        </form>
    </x-modal>

    <x-confirm-dialog wire:model="confirmingDelete" title="Delete this project?" confirm="deleteProject" button="Delete project"
        message="All of its environments, variables and activity will be permanently deleted. This cannot be undone." />
</div>
