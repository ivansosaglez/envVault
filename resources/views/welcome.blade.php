<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="font-sans">
        {{-- Header --}}
        <header class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <a href="/"><x-brand /></a>
            <div class="flex items-center gap-3">
                <x-theme-toggle />
                <x-button variant="ghost" size="sm" :href="route('login')">Log in</x-button>
                <x-button size="sm" :href="route('register')" class="hidden sm:inline-flex">Get started</x-button>
            </div>
        </header>

        <main>
            {{-- Hero --}}
            <section class="mx-auto max-w-6xl px-4 pb-16 pt-12 sm:px-6 sm:pt-20 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <x-badge variant="success" dot>Built for Laravel projects</x-badge>
                        <h1 class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">Keep your environments under control.</h1>
                        <p class="mt-5 max-w-lg text-lg text-zinc-600 dark:text-zinc-400">Compare, validate and manage your <code class="ev-mono rounded bg-zinc-100 px-1.5 py-0.5 dark:bg-zinc-800">.env</code> configuration without the guesswork.</p>
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <x-button size="lg" :href="route('register')">Get started</x-button>
                            <x-button size="lg" variant="secondary" :href="route('login')">Log in</x-button>
                        </div>
                        <p class="mt-4 text-sm text-zinc-500">Know what's missing before it breaks production.</p>
                    </div>

                    {{-- Visual: a comparison --}}
                    <div class="ev-card overflow-hidden shadow-xl shadow-zinc-900/5" aria-label="Example comparison between Local and Production">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
                            <div class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span><span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span><span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span></div>
                            <span class="text-xs text-zinc-500">Local vs Production</span>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="text-left text-xs uppercase tracking-wide text-zinc-500 [&_th]:px-4 [&_th]:py-2.5 [&_th]:font-medium">
                                <tr><th>Variable</th><th>Local</th><th>Production</th></tr>
                            </thead>
                            <tbody class="ev-mono divide-y divide-zinc-100 dark:divide-zinc-800 [&_td]:px-4 [&_td]:py-3">
                                <tr class="bg-amber-50/50 dark:bg-amber-500/5"><td class="font-medium">APP_DEBUG</td><td>true</td><td>false</td></tr>
                                <tr class="bg-amber-50/50 dark:bg-amber-500/5"><td class="font-medium">DB_HOST</td><td>localhost</td><td>db.prod</td></tr>
                                <tr><td class="font-medium">STRIPE_KEY</td><td class="text-zinc-400">configured</td><td class="text-zinc-400">configured</td></tr>
                                <tr class="bg-red-50/50 dark:bg-red-500/5"><td class="font-medium">REDIS_HOST</td><td>localhost</td><td class="font-sans text-xs font-medium text-red-600 dark:text-red-400">✗ missing</td></tr>
                            </tbody>
                        </table>
                        <div class="flex gap-2 border-t border-zinc-200 px-4 py-3 text-xs dark:border-zinc-800">
                            <x-badge variant="success">✓ 1 same</x-badge><x-badge variant="warning">⚠ 2 different</x-badge><x-badge variant="danger">✗ 1 missing</x-badge>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Problem --}}
            <section class="border-y border-zinc-200 bg-white py-16 dark:border-zinc-800 dark:bg-zinc-900/40">
                <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    <h2 class="max-w-2xl text-2xl font-semibold tracking-tight sm:text-3xl">"It works on my machine" is usually a missing variable.</h2>
                    <div class="mt-10 grid gap-8 sm:grid-cols-3">
                        @foreach ([
                            ['The deploy that 500s', 'A new feature needs STRIPE_WEBHOOK_SECRET. It lives in your local .env, and nowhere else.'],
                            ['Drift between environments', 'Staging and production quietly diverge: a different cache driver, a forgotten queue connection.'],
                            ['.env.example nobody updates', 'New teammates copy a stale example and lose an afternoon finding out what is missing.'],
                        ] as [$title, $text])
                            <div>
                                <h3 class="font-semibold">{{ $title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $text }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Features --}}
            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">One small tool, four jobs done well.</h2>
                <div class="mt-10 grid gap-5 sm:grid-cols-2">
                    @foreach ([
                        ['Compare environments', 'See what is the same, different, missing or extra between any two environments at a glance.'],
                        ['Validate any .env', 'Paste a file and get a clear report of missing and empty variables against an environment.'],
                        ['Generate .env.example', 'A deterministic, grouped example with names only. Copy it or download it.'],
                        ['Import in seconds', 'Paste a .env, review what will change, and decide per variable whether to skip or replace.'],
                    ] as [$title, $text])
                        <x-card>
                            <h3 class="font-semibold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $text }}</p>
                        </x-card>
                    @endforeach
                </div>
            </section>

            {{-- Security --}}
            <section class="border-y border-zinc-200 bg-white py-16 dark:border-zinc-800 dark:bg-zinc-900/40">
                <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Your secrets stay secret.</h2>
                        <ul class="mt-6 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                            @foreach ([
                                'Every value is encrypted at rest with Laravel\'s AES-256 encryption.',
                                'Secrets are masked in lists and comparisons. Reveal one only when you need it.',
                                'The activity log records what changed, never the values.',
                                'Strict ownership checks: you can only ever see your own projects.',
                            ] as $point)
                                <li class="flex gap-3"><span class="mt-0.5 text-emerald-600 dark:text-emerald-400">✓</span>{{ $point }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="ev-card ev-mono divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ([['APP_NAME', 'My Laravel App', false], ['APP_KEY', '', true], ['DB_PASSWORD', '', true], ['STRIPE_SECRET', '', true]] as [$key, $value, $secret])
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="font-medium">{{ $key }}</span>
                                @if ($secret)
                                    <span class="flex items-center gap-3"><span class="tracking-wider text-zinc-400">{{ \App\Models\EnvironmentVariable::MASK }}</span><x-badge variant="warning" class="font-sans">Secret</x-badge></span>
                                @else
                                    <span class="text-zinc-500">{{ $value }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- CTA --}}
            <section class="mx-auto max-w-6xl px-4 py-20 text-center sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Know what's missing.</h2>
                <p class="mx-auto mt-3 max-w-md text-zinc-600 dark:text-zinc-400">Create a project, add your environments and see the gaps in under a minute.</p>
                <div class="mt-8"><x-button size="lg" :href="route('register')">Get started</x-button></div>
            </section>
        </main>

        <footer class="border-t border-zinc-200 py-8 text-center text-xs text-zinc-500 dark:border-zinc-800">
            EnvVault · A small tool to keep Laravel environments under control.
        </footer>
    </body>
</html>
