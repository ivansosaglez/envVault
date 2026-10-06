<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="min-h-screen font-sans">
        <livewire:layout.navigation />

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            @if (isset($header))
                <div class="mb-8">{{ $header }}</div>
            @endif

            {{ $slot }}
        </main>

        <x-toaster />

        <footer class="mx-auto max-w-6xl px-4 pb-10 text-xs text-zinc-400 sm:px-6 lg:px-8 dark:text-zinc-600">
            EnvVault · Know what's missing. Keep your environments under control.
        </footer>
    </body>
</html>
