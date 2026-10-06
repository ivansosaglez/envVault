<div class="space-y-6">
    <x-page-header title=".env.example" subtitle="Variable names only. Values are never included, so it is safe to commit.">
        <x-slot name="breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">Projects</a>
            <span class="mx-1 text-zinc-300 dark:text-zinc-600">/</span>
            <a href="{{ route('projects.show', $project->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100">{{ $project->name }}</a>
        </x-slot>
    </x-page-header>

    <div class="max-w-xs">
        <x-field label="Generate from" name="environment">
            <x-select wire:model.live="environment" id="environment">
                <option value="">All environments</option>
                @foreach ($environments as $env)<option value="{{ $env->slug }}">{{ $env->name }}</option>@endforeach
            </x-select>
        </x-field>
    </div>

    @if ($output === '')
        <x-empty-state title="Nothing to generate yet." description="Add some variables first and your .env.example will appear here." />
    @else
        <div x-data="clipboard(@js($output))" class="ev-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-2.5 dark:border-zinc-800">
                <span class="ev-mono text-zinc-500">.env.example</span>
                <div class="flex gap-2">
                    <x-button variant="secondary" size="sm" x-on:click="copy()"><span x-text="copied ? 'Copied ✓' : 'Copy'">Copy</span></x-button>
                    <x-button size="sm" wire:click="download">Download</x-button>
                </div>
            </div>
            <pre class="ev-mono max-h-[32rem] overflow-auto p-4 leading-6" tabindex="0">{{ $output }}</pre>
        </div>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">Some browsers drop the leading dot of downloaded files. If that happens, rename it to <code class="ev-mono">.env.example</code>.</p>
    @endif
</div>
