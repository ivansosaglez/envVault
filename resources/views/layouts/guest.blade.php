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

            <div class="ev-card mt-8 w-full overflow-hidden p-6 sm:max-w-md sm:p-8">
                {{ $slot }}
            </div>

            <div class="mt-6"><x-theme-toggle /></div>
        </div>
    </body>
</html>
