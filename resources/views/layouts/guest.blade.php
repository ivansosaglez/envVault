<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="font-sans">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="/" wire:navigate>
                <x-brand class="text-lg" />
            </a>

            @if (config('app.public_demo'))
                <x-alert variant="warning" title="Public demo" class="mt-8 w-full sm:max-w-md">
                    This is a public sandbox. Please <strong>do not enter real secrets</strong>; anyone can create an account and the demo data is reset regularly.
                    To look around, sign in with <code class="ev-mono">demo@example.com</code> / <code class="ev-mono">password</code>.
                </x-alert>
            @endif

            <div @class(['ev-card w-full overflow-hidden p-6 sm:max-w-md sm:p-8', 'mt-4' => config('app.public_demo'), 'mt-8' => ! config('app.public_demo')])>
                {{ $slot }}
            </div>

            <div class="mt-6"><x-theme-toggle /></div>
        </div>
    </body>
</html>
