<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Compare, validate and manage your .env configuration without the guesswork.">

<title>{{ isset($title) ? "{$title} · " : '' }}{{ config('app.name') }}</title>

<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><rect width=%2232%22 height=%2232%22 rx=%228%22 fill=%22%23059669%22/><path d=%22M9 11.5h14M9 16h14M9 20.5h8%22 stroke=%22white%22 stroke-width=%222.2%22 stroke-linecap=%22round%22/></svg>">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

{{-- Apply the saved theme before first paint to avoid a flash of the wrong one. --}}
<script>
    (() => {
        try {
            const mode = localStorage.getItem('theme') ?? 'system';
            const dark = mode === 'dark' || (mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        } catch (e) {}
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
