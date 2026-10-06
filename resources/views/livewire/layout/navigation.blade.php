<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="border-b border-zinc-200 bg-white/80 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/80">
    <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('dashboard') }}" wire:navigate><x-brand /></a>

            <div class="hidden items-center gap-1 sm:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard', 'projects.*')" wire:navigate>Projects</x-nav-link>
            </div>
        </div>

        <div class="hidden items-center gap-3 sm:flex">
            <x-theme-toggle />

            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <span class="flex size-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white dark:bg-emerald-500 dark:text-zinc-950">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                        <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="border-b border-zinc-100 px-4 py-2 text-xs text-zinc-500 dark:border-zinc-800">{{ auth()->user()->email }}</div>
                    <x-dropdown-link :href="route('profile')" wire:navigate>Profile</x-dropdown-link>
                    <button wire:click="logout" class="w-full text-start">
                        <x-dropdown-link>Log out</x-dropdown-link>
                    </button>
                </x-slot>
            </x-dropdown>
        </div>

        <button @click="open = ! open" class="rounded-md p-2 text-zinc-500 hover:bg-zinc-100 sm:hidden dark:hover:bg-zinc-800" aria-label="Menu">
            <svg class="size-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak class="space-y-1 border-t border-zinc-200 px-4 py-3 sm:hidden dark:border-zinc-800">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard', 'projects.*')" wire:navigate>Projects</x-responsive-nav-link>
        <x-responsive-nav-link :href="route('profile')" wire:navigate>Profile</x-responsive-nav-link>
        <button wire:click="logout" class="w-full text-start"><x-responsive-nav-link>Log out</x-responsive-nav-link></button>
        <div class="pt-2"><x-theme-toggle /></div>
    </div>
</nav>
