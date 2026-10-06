<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head', ['title' => $code])
    </head>
    <body class="font-sans">
        <main class="flex min-h-screen flex-col items-center justify-center px-6 text-center">
            <a href="/"><x-brand /></a>
            <p class="mt-12 font-mono text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ $code }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $title }}</h1>
            <p class="mt-3 max-w-md text-zinc-600 dark:text-zinc-400">{{ $message }}</p>
            <div class="mt-8 flex gap-3">
                <x-button :href="auth()->check() ? route('dashboard') : url('/')">{{ auth()->check() ? 'Back to your projects' : 'Back to home' }}</x-button>
                <x-button variant="secondary" onclick="history.length > 1 ? history.back() : location.assign('/')">Go back</x-button>
            </div>
        </main>
    </body>
</html>
