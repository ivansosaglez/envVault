<div class="space-y-6">
    <x-page-header title="Validate .env" subtitle="Paste a .env file and check it against the variables an environment expects. Nothing is stored.">
        <x-slot name="breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">Projects</a>
            <span class="mx-1 text-zinc-300 dark:text-zinc-600">/</span>
            <a href="{{ route('projects.show', $project->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">{{ $project->name }}</a>
        </x-slot>
    </x-page-header>

    @if ($environments->isEmpty())
        <x-empty-state title="No environments to validate against." description="Add an environment with some variables first.">
            <x-button :href="route('projects.show', $project->slug)" wire:navigate>Back to project</x-button>
        </x-empty-state>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            <form wire:submit="validateEnv" class="space-y-4">
                <x-field label="Environment" name="environment">
                    <x-select wire:model.live="environment" id="environment">
                        @foreach ($environments as $env)<option value="{{ $env->slug }}">{{ $env->name }}</option>@endforeach
                    </x-select>
                </x-field>

                <x-field label=".env contents" name="content">
                    <x-textarea wire:model="content" id="content" rows="14" class="ev-mono" spellcheck="false"
                        placeholder="APP_NAME=My App&#10;APP_ENV=production&#10;DB_HOST=&#10;DB_DATABASE=myapp" />
                </x-field>

                <div class="flex items-center gap-2">
                    <x-button type="submit" wire:loading.attr="disabled">Validate</x-button>
                    @if ($content !== '') <x-button variant="ghost" wire:click="clear">Clear</x-button> @endif
                </div>
            </form>

            <div>
                @if (! $report)
                    <x-empty-state title="No results yet." description="Paste a .env on the left and press Validate." />
                @else
                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold">Configuration issues</h2>
                            @if ($report->isValid())
                                <x-badge variant="success">✓ Valid for {{ $target->name }}</x-badge>
                            @else
                                <x-badge variant="danger">{{ count($report->missing) + count($report->empty) }} to fix</x-badge>
                            @endif
                        </div>

                        @if ($report->parsed->invalidLines || $report->parsed->duplicates)
                            <x-alert variant="warning">
                                @if ($report->parsed->invalidLines) Ignored unparseable lines: {{ implode(', ', $report->parsed->invalidLines) }}. @endif
                                @if ($report->parsed->duplicates) Duplicated keys (last value used): <span class="ev-mono">{{ implode(', ', $report->parsed->duplicates) }}</span>. @endif
                            </x-alert>
                        @endif

                        @foreach ([
                            ['missing', '✗ Missing', 'danger', $report->missing, 'Expected by ' . $target->name . ' but not found.'],
                            ['empty', '⚠ Empty', 'warning', $report->empty, 'Present, but without a value.'],
                            ['unknown', '+ Not in ' . $target->name, 'info', $report->unknown, 'Found in your .env but not defined here.'],
                            ['present', '✓ Present', 'success', $report->present, null],
                        ] as [$id, $title, $variant, $keys, $hint])
                            @if ($keys)
                                <x-card class="!p-4" wire:key="section-{{ $id }}">
                                    <div class="mb-2 flex items-baseline justify-between gap-2">
                                        <x-badge :variant="$variant">{{ $title }} · {{ count($keys) }}</x-badge>
                                        @if ($hint) <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</span> @endif
                                    </div>
                                    <ul class="ev-mono flex flex-wrap gap-x-4 gap-y-1">
                                        @foreach ($keys as $key)<li>{{ $key }}</li>@endforeach
                                    </ul>
                                </x-card>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
