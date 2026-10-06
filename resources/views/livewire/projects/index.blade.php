<div>
    <x-page-header title="Your projects" subtitle="Every project groups the environments whose configuration you want to keep in sync.">
        <x-slot name="actions">
            <x-button wire:click="$set('showForm', true)">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                New project
            </x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-8">
        @if ($projects->isEmpty())
            <x-empty-state title="No projects yet." description="Create your first project to start managing your environments.">
                <x-button wire:click="$set('showForm', true)">Create project</x-button>
            </x-empty-state>
        @else
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <a href="{{ route('projects.show', $project->slug) }}" wire:navigate wire:key="project-{{ $project->id }}"
                       class="ev-card group flex flex-col p-5 transition hover:border-zinc-300 hover:shadow-md dark:hover:border-zinc-700">
                        <h2 class="truncate font-semibold">{{ $project->name }}</h2>
                        <p class="mt-0.5 line-clamp-1 min-h-5 text-sm text-zinc-500 dark:text-zinc-400">{{ $project->description }}</p>

                        <div class="mt-4 flex gap-5 text-sm text-zinc-600 dark:text-zinc-400">
                            <span><b class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $project->environments->count() }}</b> {{ Str::plural('environment', $project->environments->count()) }}</span>
                            <span><b class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $project->environments->sum(fn ($e) => $e->variables->count()) }}</b> variables</span>
                        </div>

                        <ul class="mt-4 flex-1 space-y-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            @forelse ($project->environments as $environment)
                                @php($report = $project->healthReports[$environment->id])
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <span class="flex min-w-0 items-center gap-2"><x-env-dot :color="$environment->color" /><span class="truncate">{{ $environment->name }}</span></span>
                                    @if ($report->status() === \App\Enums\HealthStatus::Healthy)
                                        <x-badge variant="success">✓ Complete</x-badge>
                                    @else
                                        <x-badge :variant="$report->status() === \App\Enums\HealthStatus::Attention ? 'warning' : 'danger'">
                                            ⚠ {{ $report->missing ? count($report->missing).' missing' : count($report->empty).' empty' }}
                                        </x-badge>
                                    @endif
                                </li>
                            @empty
                                <li class="text-sm text-zinc-500 dark:text-zinc-400">No environments yet.</li>
                            @endforelse
                        </ul>

                        <span class="mt-5 text-sm font-medium text-emerald-600 dark:text-emerald-400">Open project <span class="inline-block transition group-hover:translate-x-0.5">→</span></span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <x-modal wire:model="showForm">
        <form wire:submit="create" class="space-y-5">
            <div>
                <h2 class="text-lg font-semibold">New project</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">A project holds all the environments of one application.</p>
            </div>

            <x-field label="Name" name="name">
                <x-text-input wire:model="name" id="name" placeholder="My Laravel App" autofocus />
            </x-field>

            <x-field label="Description" name="description" hint="Optional.">
                <x-text-input wire:model="description" id="description" placeholder="Laravel application" />
            </x-field>

            <div class="flex justify-end gap-2">
                <x-button variant="secondary" x-on:click="$dispatch('close')">Cancel</x-button>
                <x-button type="submit" wire:loading.attr="disabled">Create project</x-button>
            </div>
        </form>
    </x-modal>
</div>
